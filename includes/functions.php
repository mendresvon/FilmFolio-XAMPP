<?php
// functions.php
// handles all api calls and database caching logic

require_once __DIR__ . '/dbtools.inc.php';
require_once __DIR__ . '/api_config.php';

// api helper function
// sends request to tmdb and returns the data as an array
function callTMDB($endpoint, $params = []) {
    // add api key to parameters
    $params['api_key'] = TMDB_API_KEY;
    $params['language'] = 'en-US'; // default to english
    
    // build the full url
    $url = "https://api.themoviedb.org/3" . $endpoint . "?" . http_build_query($params);

    // initialize curl to make api calls
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // fixes ssl issues on local xampp
    
    $response = curl_exec($ch);
    curl_close($ch);

    // decode json response into a php array
    return json_decode($response, true);
}

// display functions (fetch data)

// get a list of popular movies for the homepage
function getPopularMovies() {
    $data = callTMDB('/movie/popular');
    return $data['results'] ?? []; // return results or empty array if error
}

// search for movies based on user input
function searchMovies($query) {
    $data = callTMDB('/search/movie', ['query' => $query]);
    return $data['results'] ?? [];
}

// get full details for a specific movie
function getMovieDetails($tmdb_id) {
    return callTMDB("/movie/" . $tmdb_id);
}

// database cache functions

// ensures a movie exists in our local sql database
// if not, it fetches it from api and inserts it
// called before adding to watchlist or reviews
function ensureMovieInLocalDB($tmdb_id) {
    global $link; // use the connection from dbtools.inc.php

    // check if movie already exists in our db
    $sql = "SELECT movie_id FROM movies WHERE tmdb_id = ?";
    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param($stmt, "i", $tmdb_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {
        // movie exists, we are good
        return true;
    }

    // if not in db, fetch details from api
    $movie = getMovieDetails($tmdb_id);
    
    if (!$movie) return false; // api failed

    // prepare data for insert
    $title = $movie['title'];
    $overview = $movie['overview'];
    $release_date = $movie['release_date'];
    $poster_path = $movie['poster_path'];

    // insert into local db
    $insert_sql = "INSERT INTO movies (tmdb_id, title, overview, release_date, poster_path) VALUES (?, ?, ?, ?, ?)";
    $insert_stmt = mysqli_prepare($link, $insert_sql);
    mysqli_stmt_bind_param($insert_stmt, "issss", $tmdb_id, $title, $overview, $release_date, $poster_path);
    
    return mysqli_stmt_execute($insert_stmt);
}
?>