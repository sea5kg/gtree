<!doctype html>
<html lang="ru">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <script src="./js/jquery-3.7.1.min.js"></script>
    <script src="./js/popper.min.js"></script>
    <script src="./js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="./css/bootstrap.min.css">
    <style>
body {
  height: 100%;
}

.genealogical-tree {
    position: fixed;
    border: 1px solid black;
    left: 10px;
    width: calc(100% - 20px);
    height: calc(100% - 60px);
    overflow: scroll;
}

.gtree-container {
    border: 1px solid black;
    height: calc(100% - 180px);
    overflow: scroll;
    left: 10px;
    width: calc(100% - 20px);
    position: fixed;
}
    </style>

    <title>Генеалогическое древо</title>
  </head>
  <body>
      <nav class="navbar navbar-expand-lg navbar-light bg-light">
          <a class="navbar-brand" href="#">Генеологическое древо</a>
          <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
          </button>

          <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav mr-auto">
              <li class="nav-item active">
                <a class="nav-link" href="index.php">Дерево</a>
              </li>
              <li class="nav-item active">
                <a class="nav-link" href="index2.php">Дерево 2</a>
              </li>
              <li class="nav-item active">
                <a class="nav-link" href="persons.php">Персоны</a>
              </li>
              <li class="nav-item active">
                <a class="nav-link" href="contact.php">Обратная связь</a>
              </li>
            </ul>
          </div>
        </nav>