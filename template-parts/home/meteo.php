<section class="weather-section">
  <div class="container">

    <h2 class="weather-title"></h2>

    <div class="weather-grid">

      <!-- OGGI -->
      <div class="weather-card big">
        <div class="weather-day">OGGI - 20/3/2026</div>

        <div class="weather-content">
          <div class="weather-info">
            <div>Temp. media 6.75°C</div>
            <div>Temp. massima 10.18°C</div>
            <div>Temp. minima 6.75°C</div>
            <div>Umidità 59%</div>
          </div>

          <div class="weather-icon">☀️</div>
        </div>

        <div class="weather-desc">cielo sereno</div>
      </div>

      <!-- GIORNI -->
      <div class="weather-card">
        <div class="weather-day">SABATO</div>
        <div class="weather-icon">☁️</div>
        <div class="weather-desc">cielo coperto</div>
      </div>

      <div class="weather-card">
        <div class="weather-day">DOMENICA</div>
        <div class="weather-icon">🌧️</div>
        <div class="weather-desc">pioggia moderata</div>
      </div>

      <div class="weather-card">
        <div class="weather-day">LUNEDÌ</div>
        <div class="weather-icon">⛅</div>
        <div class="weather-desc">nubi sparse</div>
      </div>

      <div class="weather-card">
        <div class="weather-day">MARTEDÌ</div>
        <div class="weather-icon">☁️</div>
        <div class="weather-desc">cielo coperto</div>
      </div>

    </div>
  </div>
</section>
<style>

.weather-section {
  padding: 60px 0;
}

.weather-title {
  font-size: 28px;
  margin-bottom: 30px;
}

.weather-grid {
  display: flex;
  gap: 20px;
  flex-wrap: wrap;
}

.weather-card {
  background: #f5f5f5;
  border-radius: 10px;
  padding: 20px;
  text-align: center;
  flex: 1;
  min-width: 180px;
  box-shadow: 0 10px 30px rgba(0,0,0,0.05);
}

.weather-card.big {
  flex: 2;
  min-width: 300px;
}

.weather-day {
  font-weight: bold;
  margin-bottom: 10px;
}

.weather-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.weather-info {
  text-align: left;
  font-size: 14px;
}

.weather-icon {
  font-size: 40px;
}

.weather-desc {
  margin-top: 10px;
  font-size: 14px;
}

/* mobile */
@media (max-width: 768px) {
  .weather-grid {
    flex-direction: column;
  }
}

  
</style>
