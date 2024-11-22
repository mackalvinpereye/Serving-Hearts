import pandas as pd
import matplotlib.pyplot as plt
import matplotlib.dates as mdates
from statsmodels.tsa.statespace.sarimax import SARIMAX
from datetime import datetime
import sys

# Check for command-line argument
if len(sys.argv) > 1:
    forecast_steps = int(sys.argv[1])
else:
    raise ValueError("Forecast steps not provided as a command-line argument.")

# Load and prepare historical data
data = pd.read_csv('C:/XAMPP/htdocs/Serving Hearts/Predictions/blood_inventory_handover.csv', parse_dates=['Month'], index_col='Month').asfreq('MS')

# Define SARIMA model order parameters
order = (1, 0, 0)
seasonal_order = (0, 1, 0, 12)

# Fit SARIMA model
model = SARIMAX(data['Total Given Units'], order=order, seasonal_order=seasonal_order)
results = model.fit(disp=False)

# Determine forecast start date and generate forecast
forecast_start_date = max(data.index[-1] + pd.DateOffset(months=1), pd.Timestamp(datetime.now().strftime('%Y-%m-01')))
forecast_dates = pd.date_range(start=forecast_start_date, periods=forecast_steps, freq='MS')
forecast = results.get_forecast(steps=forecast_steps)
forecast_df = forecast.summary_frame()
forecast_df.index = forecast_dates

# Save forecast data to CSV
forecast_df.to_csv('sample_forecast_file_totalgivenunits.csv', columns=['mean', 'mean_ci_lower', 'mean_ci_upper'])

# Combine observed and forecasted data
combined_data = pd.concat([data['Total Given Units'], forecast_df['mean']])

# Plotting
plt.figure(figsize=(12, 6))
plt.plot(combined_data, label='Observed + Forecast', color='blue', linestyle='-', marker='o')
plt.plot(forecast_df['mean'], label='Forecast', color='orange', linestyle='--', marker='o')
plt.fill_between(forecast_df.index, forecast_df['mean_ci_lower'], forecast_df['mean_ci_upper'], 
                 color='orange', alpha=0.3, label='Forecast Range (Confidence Interval)')

# Format the x-axis
ax = plt.gca()
ax.xaxis.set_major_locator(mdates.MonthLocator())
ax.xaxis.set_major_formatter(mdates.DateFormatter('%b %Y'))
plt.xticks(rotation=45)

# Labels, grid, legend
plt.title('Monthly Observed and Forecasted Blood Handover')
plt.xlabel('Month')
plt.ylabel('Blood Handover')
plt.grid(visible=True, which='major', linestyle='--', linewidth=0.5)
plt.legend()
plt.tight_layout()
plt.savefig('C:/XAMPP/htdocs/Serving Hearts/Predictions/graphs/blood_handover_forecast.png')
plt.close()
