<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jour1</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body>
<iframe src="../header.html" width="100%" height="100" frameborder="0"></iframe>
<div class="text-xl p-24 space-y-10">
    <h1>Deux fois deux</h1>
      <?php
      for ($i=1; $i<10; $i++) {
        echo '<p>';
        echo "2 x $i = ", 2*$i;
        echo '</p>';
      }
      ?>
</div>
</body>
</html>


