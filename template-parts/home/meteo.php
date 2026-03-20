<section class="weather-section">
  <div class="container">

    <h2 class="weather-title">Meteo</h2>

    <div class="weather-grid"></div>

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

.weather-icon img {
  width: 60px;
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

            const daily = {};

            // Raggruppa per giorno
            data.list.forEach(item => {
                const date = item.dt_txt.split(" ")[0];

                if (!daily[date]) {
                    daily[date] = {
                        temps: [],
                        humidity: [],
                        icons: [],
                        desc: item.weather[0].description
                    };
                }

                daily[date].temps.push(item.main.temp);
                daily[date].humidity.push(item.main.humidity);
                daily[date].icons.push(item.weather[0].icon);
            });

            const days = Object.keys(daily).slice(0, 5);

            days.forEach((day, index) => {

                const d = daily[day];

                const avgTemp = Math.round(d.temps.reduce((a,b)=>a+b)/d.temps.length);
                const minTemp = Math.round(Math.min(...d.temps));
                const maxTemp = Math.round(Math.max(...d.temps));
                const avgHumidity = Math.round(d.humidity.reduce((a,b)=>a+b)/d.humidity.length);

                const icon = d.icons[Math.floor(d.icons.length/2)];

                const dateObj = new Date(day);
                const dayName = index === 0 
                    ? "OGGI" 
                    : dateObj.toLocaleDateString("it-IT", { weekday: 'long' }).toUpperCase();

                const html = `
                    <div class="weather-card ${index === 0 ? 'big' : ''}">
                        <div class="weather-day">${dayName}</div>

                        <div class="weather-content">
                            <div class="weather-info">
                                <div>Temp. media ${avgTemp}°C</div>
                                <div>Max ${maxTemp}°C</div>
                                <div>Min ${minTemp}°C</div>
                                <div>Umidità ${avgHumidity}%</div>
                            </div>

                            <div class="weather-icon">
                                <img src="https://openweathermap.org/img/wn/${icon}@2x.png">
                            </div>
                        </div>

                        <div class="weather-desc">${d.desc}</div>
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
  
</style>
