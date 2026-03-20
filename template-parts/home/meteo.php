<section class="weather-section">
  <div class="container">

    <h2 class="weather-title"></h2>

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

    const container = document.querySelector(".weather-grid");

    const city = "Venetico,IT";
    const apiKey = "062a482b6456a7f66cfdec432a930862";

    const apiUrl = `https://api.openweathermap.org/data/2.5/forecast?q=${city}&appid=${apiKey}&units=metric&lang=it`;

    async function updateWeather() {
        try {
            const response = await fetch(apiUrl);
            const data = await response.json();

            container.innerHTML = "";

            const dailyData = {};

            // Raggruppa per giorno (prende circa le 12:00)
            data.list.forEach(item => {
                const date = item.dt_txt.split(" ")[0];

                if (!dailyData[date] && item.dt_txt.includes("12:00:00")) {
                    dailyData[date] = item;
                }
            });

            const days = Object.keys(dailyData).slice(0, 5);

            days.forEach((day, index) => {

                const item = dailyData[day];
                const temp = Math.round(item.main.temp);
                const desc = item.weather[0].description;
                const icon = item.weather[0].icon;

                const dateObj = new Date(day);
                const dayName = index === 0 
                    ? "OGGI" 
                    : dateObj.toLocaleDateString("it-IT", { weekday: 'long' }).toUpperCase();

                const html = `
                    <div class="weather-card ${index === 0 ? 'big' : ''}">
                        <div class="weather-day">${dayName}</div>
                        <div class="weather-icon">
                            <img src="https://openweathermap.org/img/wn/${icon}@2x.png">
                        </div>
                        <div class="weather-temp">${temp}°C</div>
                        <div class="weather-desc">${desc}</div>
                    </div>
                `;

                container.innerHTML += html;
            });

        } catch (error) {
            console.error("Errore meteo:", error);
        }
    }

    updateWeather();
});
</script>
