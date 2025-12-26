<?php
// functions.php
// This file handles all API calls and Database "Caching" logic

require_once 'dbtools.inc.php';
require_once 'api_config.php';

// ==========================================
// 1. API HELPER FUNCTION
// ==========================================
// A simple function to send requests to TMDB and return the data as an array
function callTMDB($endpoint, $params = []) {
    // Add API Key to parameters
    $params['api_key'] = TMDB_API_KEY;
    $params['language'] = 'en-US'; // Default to English
    
    // Build the full URL
    $url = "https://api.themoviedb.org/3" . $endpoint . "?" . http_build_query($params);

    // Initialize cURL (Standard way to make API calls in PHP)
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Fixes SSL issues on local XAMPP
    
    $response = curl_exec($ch);
    curl_close($ch);

    // Decode JSON response into a PHP Array
    return json_decode($response, true);
}

// ==========================================
// 2. DISPLAY FUNCTIONS (FETCH DATA)
// ==========================================

// Get a list of popular movies for the Homepage
function getPopularMovies() {
    $data = callTMDB('/movie/popular');
    return $data['results'] ?? []; // Return results or empty array if error
}

// Search for movies based on user input
function searchMovies($query) {
    $data = callTMDB('/search/movie', ['query' => $query]);
    return $data['results'] ?? [];
}

// Get full details for a specific movie
function getMovieDetails($tmdb_id) {
    return callTMDB("/movie/" . $tmdb_id);
}

// ==========================================
// 3. DATABASE "CACHE" FUNCTIONS
// ==========================================

/**
 * Ensures a movie exists in our local SQL database.
 * If it's not there, it fetches it from API and INSERTs it.
 * This is called BEFORE adding to Watchlist or Reviews.
 */
function ensureMovieInLocalDB($tmdb_id) {
    global $link; // Use the connection from dbtools.inc.php

    // 1. Check if movie already exists in our DB
    $sql = "SELECT movie_id FROM movies WHERE tmdb_id = ?";
    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param($stmt, "i", $tmdb_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {
        // Movie exists, we are good.
        return true;
    }

    // 2. If NOT in DB, fetch details from API
    $movie = getMovieDetails($tmdb_id);
    
    if (!$movie) return false; // API failed

    // Prepare data for Insert
    $title = $movie['title'];
    $overview = $movie['overview'];
    $release_date = $movie['release_date'];
    $poster_path = $movie['poster_path'];

    // 3. Insert into local DB
    $insert_sql = "INSERT INTO movies (tmdb_id, title, overview, release_date, poster_path) VALUES (?, ?, ?, ?, ?)";
    $insert_stmt = mysqli_prepare($link, $insert_sql);
    mysqli_stmt_bind_param($insert_stmt, "issss", $tmdb_id, $title, $overview, $release_date, $poster_path);
    
    return mysqli_stmt_execute($insert_stmt);
}
?>