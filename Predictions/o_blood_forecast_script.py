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


# Load forecast and observed data
O_plus_forecast = pd.read_csv('C:/XAMPP/htdocs/Serving Hearts/Predictions/forecast_file_O+.csv', parse_dates=['Date'], index_col='Date')
O_minus_forecast = pd.read_csv('C:/XAMPP/htdocs/Serving Hearts/Predictions/forecast_file_O-.csv', parse_dates=['Date'], index_col='Date')
O_plus_observed = pd.read_csv('C:/XAMPP/htdocs/Serving Hearts/Predictions/data_folder/blood_inventory_Opos.csv', parse_dates=['Month'], index_col='Month')
O_minus_observed = pd.read_csv('C:/XAMPP/htdocs/Serving Hearts/Predictions/data_folder/blood_inventory_Oneg.csv', parse_dates=['Month'], index_col='Month')

# Rename columns for clarity
O_plus_observed = O_plus_observed.rename(columns={'O+': 'Observed_O_plus'})
O_minus_observed = O_minus_observed.rename(columns={'O-': 'Observed_O_minus'})

# Combine the forecast datasets
combined_forecast = pd.merge(
    O_plus_forecast[['mean', 'mean_ci_lower', 'mean_ci_upper']], 
    O_minus_forecast[['mean', 'mean_ci_lower', 'mean_ci_upper']], 
    left_index=True, 
    right_index=True, 
    suffixes=('_O_plus', '_O_minus')
)

# Combine observed data with forecast data
combined_data = pd.merge(
    combined_forecast, 
    O_plus_observed[['Observed_O_plus']], 
    left_index=True, 
    right_index=True, 
    how='outer'
)
combined_data = pd.merge(
    combined_data, 
    O_minus_observed[['Observed_O_minus']], 
    left_index=True, 
    right_index=True, 
    how='outer'
)

# Concatenate observed and forecast data for combined series
combined_data['Combined_O_plus'] = combined_data['Observed_O_plus'].combine_first(combined_data['mean_O_plus'])
combined_data['Combined_O_minus'] = combined_data['Observed_O_minus'].combine_first(combined_data['mean_O_minus'])

# Define SARIMA model order parameters for O+ and O-
order = (1, 0, 0)
seasonal_order = (0, 1, 0, 12)

# Fit SARIMA model for O+ (as an example)
model_O_plus = SARIMAX(combined_data['Combined_O_plus'], order=order, seasonal_order=seasonal_order)
results_O_plus = model_O_plus.fit(disp=False)

# Fit SARIMA model for O- (as an example)
model_O_minus = SARIMAX(combined_data['Combined_O_minus'], order=order, seasonal_order=seasonal_order)
results_O_minus = model_O_minus.fit(disp=False)

# Determine forecast start date and generate forecast for O+ and O-
forecast_start_date = max(combined_data.index[-1] + pd.DateOffset(months=1), pd.Timestamp(datetime.now().strftime('%Y-%m-01')))
forecast_dates = pd.date_range(start=forecast_start_date, periods=forecast_steps, freq='MS')

forecast_O_plus = results_O_plus.get_forecast(steps=forecast_steps)
forecast_O_minus = results_O_minus.get_forecast(steps=forecast_steps)

forecast_O_plus_df = forecast_O_plus.summary_frame()
forecast_O_minus_df = forecast_O_minus.summary_frame()

forecast_O_plus_df.index = forecast_dates
forecast_O_minus_df.index = forecast_dates

# Save forecast data to CSV
forecast_O_plus_df.to_csv('C:/XAMPP/htdocs/Serving Hearts/SARIMA/forecast_file_O_plus_sarima.csv', columns=['mean', 'mean_ci_lower', 'mean_ci_upper'])
forecast_O_minus_df.to_csv('C:/XAMPP/htdocs/Serving Hearts/SARIMA/forecast_file_O_minus_sarima.csv', columns=['mean', 'mean_ci_lower', 'mean_ci_upper'])

# Append NaN to observed data to match forecast length
combined_data = combined_data.reindex(combined_data.index.union(forecast_dates))

# Combine observed and forecasted data
combined_data['Combined_O_plus'] = combined_data['Observed_O_plus'].combine_first(combined_data['mean_O_plus'])
combined_data['Combined_O_minus'] = combined_data['Observed_O_minus'].combine_first(combined_data['mean_O_minus'])

# Plotting
plt.figure(figsize=(14, 8))

# Plot observed data
plt.plot(combined_data.index, combined_data['Observed_O_plus'], label='O+ Observed', color='blue', linestyle='-', marker='o')
plt.plot(combined_data.index, combined_data['Observed_O_minus'], label='O- Observed', color='orange', linestyle='-', marker='o')

# Plot forecasted data with confidence intervals
plt.plot(combined_data.index, combined_data['mean_O_plus'], label='O+ Forecast', color='blue', linestyle='--', marker='D', markersize=7)
plt.fill_between(
    combined_data.index, 
    combined_data['mean_ci_lower_O_plus'], 
    combined_data['mean_ci_upper_O_plus'], 
    color='blue', 
    alpha=0.2, 
    label='O+ Forecast Range'
)
plt.plot(combined_data.index, combined_data['mean_O_minus'], label='O- Forecast', color='orange', linestyle='--', marker='D', markersize=7)
plt.fill_between(
    combined_data.index, 
    combined_data['mean_ci_lower_O_minus'], 
    combined_data['mean_ci_upper_O_minus'], 
    color='orange', 
    alpha=0.2, 
    label='O- Forecast Range'
)

# Plot combined (observed + forecast)
plt.plot(combined_data.index, combined_data['Combined_O_plus'], label='O+ Combined (Observed + Forecast)', color='darkblue', linestyle='-', marker='o', alpha=0.6)
plt.plot(combined_data.index, combined_data['Combined_O_minus'], label='O- Combined (Observed + Forecast)', color='darkorange', linestyle='-', marker='o', alpha=0.6)

# Format x-axis to show only month-year
plt.gca().xaxis.set_major_locator(mdates.MonthLocator())
plt.gca().xaxis.set_major_formatter(mdates.DateFormatter('%b %Y'))
plt.xticks(rotation=45)  # Rotate dates for readability

# Add title, labels, grid, and legend
plt.title('Monthly Forecasted and Observed O+ and O- Blood Handover')
plt.xlabel('Date')
plt.ylabel('Blood Handover Count')
plt.grid(visible=True, which='major', linestyle='--', linewidth=0.5)
plt.legend()
plt.tight_layout()

# Save and show the plot
plt.savefig('C:/XAMPP/htdocs/Serving Hearts/Predictions/graphs/o_blood_handover_forecast.png')
plt.show()