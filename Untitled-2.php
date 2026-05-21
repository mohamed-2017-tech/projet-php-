$host = getenv('DB_HOST') ?: '127.0.0.1'; // Utilise '127.0.0.1' car DB_HOST est vide
$user = getenv('DB_USER') ?: 'root';      // Utilise 'root' car DB_USER est vide
$password = getenv('DB_PASS') ?: '';      // Utilise '' car DB_PASS est vide
