<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);


// Database configuration
$host = 'localhost';
$db = 'lottery_test';
$user = 'chrislocal';
$pass = 'password123';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

// Function to scrape data
function scrapeData($url) {
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    //curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');

    $response = curl_exec($curl);
    curl_close($curl);

    // Debug: Output the raw response
    // echo "<pre>" . htmlspecialchars($response) . "</pre>";
    // exit; // Stop execution for debugging

    //echo "<pre>" . $response . "</pre>"; // Remove htmlspecialchars

    
    // Load HTML and parse it
    $dom = new DOMDocument();
    @$dom->loadHTML($response);
    $xpath = new DOMXPath($dom);
    
    // Adjust the XPath based on the structure of the website
    // $results = $xpath->query("//div[@class='result']"); // Modify this XPath to match the target data
    // $data = [];

    // Get all result cards
    $results = $xpath->query("//div[contains(@class, 'card')]"); // Make sure this points to the correct container
    $data = [];
    $uniqueResults = []; // Array to store unique results based on result_number

    foreach ($results as $result) {
        // $date = $xpath->query(".//span[@class='date']", $result)->item(0)->nodeValue; // Adjust XPath
        // $numbers = $xpath->query(".//span[@class='numbers']", $result)->item(0)->nodeValue; // Adjust XPath
        
        // $data[] = [
        //     'draw_date' => date('Y-m-d', strtotime($date)),
        //     'numbers' => trim($numbers),
        // ];

        // Extracting the lotto type (e.g., "7/49")
        $lottoTypeNode = $xpath->query(".//span[contains(@class, 'badge')]", $result);
        $lottoType = $lottoTypeNode->item(0)->nodeValue ?? '';

        // Only proceed if the lotto type is "7/49"
        if (trim($lottoType) !== "7/49") {
            continue; // Skip this iteration if it's not 7/49
        }

        // Extracting the draw date
        $dateNode = $xpath->query(".//h6[contains(@class, 'card-subtitle')]", $result);
        $dateText = $dateNode->item(0)->nodeValue ?? '';

        // Split the date and time
        $dateParts = preg_split('/\s*\|\s*/', trim($dateText));
        if (isset($dateParts[0])) {
            $drawDate = date('Y-m-d', strtotime($dateParts[0]));
        }

        //$drawDate = isset($dateParts[0]) ? date('Y-m-d', strtotime($dateParts[0])) : null;

        $drawTime = isset($dateParts[1]) ? trim($dateParts[1]) : null; // Extract time
        
        // Extracting the result number (e.g., #19986)
        $resultNumberNode = $xpath->query(".//h6/a[contains(@href, '/results/')]", $result);
        $resultNumber = $resultNumberNode->item(0)->nodeValue ?? '';

        // Extracting the winning numbers
        $numbers = [];
        $numberNodes = $xpath->query(".//span[contains(@class, 'badge-pill')]", $result);
        foreach ($numberNodes as $numberNode) {
            $numbers[] = trim($numberNode->nodeValue);
        }

        // Create a unique key based on draw date, time, and result number
        $uniqueKey = $drawDate . '|' . $drawTime . '|' . $resultNumber;

         // Check if the result is unique before adding
         if (!isset($uniqueResults[$uniqueKey])) {
            $data[] = [
                'draw_date' => $drawDate,
                'draw_time' => $drawTime, // Add draw time to the output
                'result_number' => $resultNumber,
                'numbers' => implode(", ", $numbers),
                'lotto_type' => $lottoType // Optional: include the lotto type if needed
            ];
            $uniqueResults[$uniqueKey] = true; // Mark this combination as seen
        }
    }

    return $data;
}

// Function to insert data into the database
function insertData($pdo, $data) {
    $stmt = $pdo->prepare("INSERT INTO lottery_results (draw_date, draw_time, result_number, numbers, lotto_type) VALUES (:draw_date, :draw_time, :result_number, :numbers, :lotto_type)");
    
    foreach ($data as $row) {
        $stmt->execute([
            ':draw_date' => $row['draw_date'],
            ':draw_time' => $row['draw_time'],
            ':result_number' => $row['result_number'],
            ':numbers' => $row['numbers'],
            ':lotto_type' => $row['lotto_type'],
        ]);
    }
}

try {
    // Create a new PDO instance
    $pdo = new PDO($dsn, $user, $pass, $options);

    // Scrape the data
    $url = 'https://gosloto.app/results/7x49';
    $scrapedData = scrapeData($url);

    // Insert data into the database
    insertData($pdo, $scrapedData);
    
    // Output the collected data in JSON format
    echo json_encode($scrapedData, JSON_PRETTY_PRINT);

} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}

?>
