<section class="weather-section">
  <div class="container">

    <h2 class="weather-title">Meteo</h2>

    <div class="weather-grid">

      <!-- OGGI -->
      <div class="weather-card big">
        <div class="weather-day" id="weather-date">OGGI</div>

        <div class="weather-content">
          <div class="weather-info">
            <div id="temperature">--°C</div>
            <div id="condition">--</div>
            <div id="location">--</div>
          </div>

          <img id="icon" src="" alt="" class="weather-img">
        </div>
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
}

.weather-card {
  background: #f5f5f5;
  border-radius: 10px;
  padding: 25px;
  flex: 1;
  box-shadow: 0 10px 30px rgba(0,0,0,0.05);
}

.weather-card.big {
  max-width: 500px;
}

.weather-day {
  font-weight: bold;
  margin-bottom: 15px;
}

.weather-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.weather-info {
  font-size: 16px;
}

.weather-img {
  width: 80px;
}

@media (max-width: 768px) {
  .weather-grid {
    flex-direction: column;
  }
}
  
</style>

<script>
document.addEventListener("DOMContentLoaded", function() {

    const weatherIcon = document.getElementById('icon');
    const temperatureElement = document.getElementById('temperature');
    const conditionElement = document.getElementById('condition');
    const locationElement = document.getElementById('location');
    const dateElement = document.getElementById('weather-date');

    const city = "Venetico, IT";
    const apiKey = "062a482b6456a7f66cfdec432a930862";

    const apiUrl = `https://api.openweathermap.org/data/2.5/weather?q=${city}&appid=${apiKey}&units=metric&lang=it`;

    async function updateWeather() {
        try {
            const response = await fetch(apiUrl);
            const data = await response.json();

            const temperature = data.main.temp;
            const condition = data.weather[0].description;
            const iconCode = data.weather[0].icon;

            temperatureElement.textContent = `${Math.round(temperature)}°C`;
            conditionElement.textContent = condition.charAt(0).toUpperCase() + condition.slice(1);
            locationElement.textContent = `${data.name}, ${data.sys.country}`;

            weatherIcon.src = `https://openweathermap.org/img/wn/${iconCode}@2x.png`;

            // DATA
            const today = new Date();
            const formattedDate = today.toLocaleDateString('it-IT');
            dateElement.textContent = `OGGI - ${formattedDate}`;

        } catch (error) {
            console.error('Errore meteo:', error);
        }
    }

    updateWeather();
});
</script>
