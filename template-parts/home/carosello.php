   <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    
    <!-- Aggiungi uno stile di base se necessario -->
    <style>
        .carousel-item img {
            max-height: 500px; /* Impostazione opzionale per la massima altezza delle immagini */
            object-fit: cover;
        }
    </style>
<div id="caroselloHome" class="carousel slide" data-ride="carousel">
  <ol class="carousel-indicators">
    <li data-target="#caroselloHome" data-slide-to="0" class="active"></li>
    <li data-target="#caroselloHome" data-slide-to="1"></li>
    <li data-target="#caroselloHome" data-slide-to="2"></li>
  </ol>
  <div class="carousel-inner">
    <div class="carousel-item active">
      <img class="d-block w-100" src="https://www.servizipa.cloud/comuni/img/comune/album_pd/3075_142.jpg" alt="First slide">
    </div>
    <div class="carousel-item">
      <img class="d-block w-100" src="https://www.servizipa.cloud/comuni/img/comune/album_pd/3075_142.jpg" alt="Second slide">
    </div>
    <div class="carousel-item">
      <img class="d-block w-100" src="https://www.servizipa.cloud/comuni/img/comune/album_pd/3075_142.jpg" alt="Third slide">
    </div>
  </div>
  <a class="carousel-control-prev" href="#caroselloHome" role="button" data-slide="prev">
    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
    <span class="sr-only">Previous</span>
  </a>
  <a class="carousel-control-next" href="#caroselloHome" role="button" data-slide="next">
    <span class="carousel-control-next-icon" aria-hidden="true"></span>
    <span class="sr-only">Next</span>
  </a>
</div>
<!-- Bootstrap JS, Popper.js, and jQuery -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
