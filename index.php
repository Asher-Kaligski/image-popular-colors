<?php

$popularColors = NULL;

if (isset($_POST['submit'])) {

    $allowedExtensions = array("jpeg", "jpg", "png");

    $temp = explode(".", $_FILES["file"]["name"]);
    $extension = end($temp);

    // Check if file extension is allowed
    if (in_array($extension, $allowedExtensions)) {

        // Remove all files from the upload dir
        $files = glob('upload/*');
        foreach ($files as $file) {

            unlink($file);
        }

        // Move the new image to the upload dir
        $imgPath = "upload/" . $_FILES["file"]["name"];
        move_uploaded_file($_FILES["file"]["tmp_name"],
            $imgPath);

        // Create a new image from file -> source: https://www.php.net/manual/en/function.imagecreatefromjpeg.php
        if ($extension === 'png')
            $img = imagecreatefrompng($imgPath);
        else
            $img = imagecreatefromjpeg($imgPath);
        

        // Get image width -> source: https://www.php.net/manual/en/function.imagesx.php
        $imgWidth = imagesx($img);
        // Get image height -> source: https://www.php.net/manual/en/function.imagesy.php
        $imgHeight = imagesy($img);

        $rgbColors = array();
        // Fill out the $rgbColors array with key as color index and value as color's index amount
        for ($x = 0; $x < $imgWidth; $x++) {
            for ($y = 0; $y < $imgHeight; $y++) {

                // Get the index of the color of a pixel -> source: https://www.php.net/manual/en/function.imagecolorat.php
                $rgb = imagecolorat($img, $x, $y);

                if (array_key_exists($rgb, $rgbColors))
                    $rgbColors[$rgb] += 1;
                else
                    $rgbColors[$rgb] = 1;

            }
        }

        arsort($rgbColors);

        $popularColors = array_slice($rgbColors, 0, 5, true);

        $imgPixelsAmount = $imgWidth * $imgHeight;

        foreach ($popularColors as $key => $value) {

            // Calculate color coverage in percentage and set as value
            $colorCoverage = ($value / $imgPixelsAmount) * 100;
            $popularColors[$key] = number_format((float) $colorCoverage, 2, '.', '');
        }

    } else {
        echo "Invalid image format. Allowed the following formats: 'jpeg', 'jpg', 'png'";
    }
}

?>

<html>

<head>
    <title>Image Popular Colors</title>
</head>

<body>

    <form action="index.php" enctype="multipart/form-data" method="post">
        Select image :
        <input type="file" name="file"><br>
        <input type="submit" value="Upload" name="submit">
    </form>
    <br>

    <?php

if ($popularColors) {

    $img = "upload/" . $_FILES["file"]["name"];
    echo '<img src="' . $img . '" height=300 width=400><br>';

    foreach ($popularColors as $key => $value) {

        // Get rgb values -> source: https://www.php.net/manual/en/function.imagecolorat.php
        $r = ($key >> 16) & 0xFF;
        $g = ($key >> 8) & 0xFF;
        $b = $key & 0xFF;

        echo "<div style='background-color:rgb($r, $g, $b);width:400px;padding:10px 0px;text-align:center;'>" . $value . "%</div>";
        echo "R:" . $r . " ";
        echo "G:" . $g . " ";
        echo "B:" . $b;
    }
}
?>

</body>

</html>