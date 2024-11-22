import pandas as pd
import matplotlib.pyplot as plt
import matplotlib.dates as mdates
from statsmodels.tsa.statespace.sarimax import SARIMAX
from datetime import datetime
import sys

# Check for command-line argument
forecast_step = 12

# Load forecast and observed data for A+ and A-
A_plus_forecast = pd.read_csv('C:/XAMPP/htdocs/Serving Hearts/Predictions/forecast_file_A+.csv', parse_dates=['Date'], index_col='Date')
A_minus_forecast = pd.read_csv('C:/XAMPP/htdocs/Serving Hearts/Predictions/forecast_file_A-.csv', parse_dates=['Date'], index_col='Date')
A_plus_observed = pd.read_csv('C:/XAMPP/htdocs/Serving Hearts/Predictions/data_folder/blood_inventory_Apos.csv', parse_dates=['Month'], index_col='Month')
A_minus_observed = pd.read_csv('C:/XAMPP/htdocs/Serving Hearts/Predictions/data_folder/blood_inventory_Aneg.csv', parse_dates=['Month'], index_col='Month')

# Rename columns for clarity
A_plus_observed = A_plus_observed.rename(columns={'A+': 'Observed_A_plus'})
A_minus_observed = A_minus_observed.rename(columns={'A-': 'Observed_A_minus'})

# Combine the forecast datasets
combined_forecast = pd.merge(
    A_plus_forecast[['mean', 'mean_ci_lower', 'mean_ci_upper']], 
    A_minus_forecast[['mean', 'mean_ci_lower', 'mean_ci_upper']], 
    left_index=True, 
    right_index=True, 
    suffixes=('_A_plus', '_A_minus')
)

# Combine observed data with forecast data
combined_data = pd.merge(
    combined_forecast, 
    A_plus_observed[['Observed_A_plus']], 
    left_index=True, 
    right_index=True, 
    how='outer'
)
combined_data = pd.merge(
    combined_data, 
    A_minus_observed[['Observed_A_minus']], 
    left_index=True, 
    right_index=True, 
    how='outer'
)

# Concatenate observed and forecast data for combined series
combined_data['Combined_A_plus'] = combined_data['Observed_A_plus'].combine_first(combined_data['mean_A_plus'])
combined_data['Combined_A_minus'] = combined_data['Observed_A_minus'].combine_first(combined_data['mean_A_minus'])

# Define SARIMA model order parameters for A+ and A-
order = (1, 0, 0)
seasonal_order = (0, 1, 0, 12)

# Fit SARIMA model for A+ (as an example)
model_A_plus = SARIMAX(combined_data['Combined_A_plus'], order=order, seasonal_order=seasonal_order)
results_A_plus = model_A_plus.fit(disp=False)

# Fit SARIMA model for A- (as an example)
model_A_minus = SARIMAX(combined_data['Combined_A_minus'], order=order, seasonal_order=seasonal_order)
results_A_minus = model_A_minus.fit(disp=False)

# Determine forecast start date and generate forecast for A+ and A-
forecast_start_date = max(combined_data.index[-1] + pd.DateOffset(months=1), pd.Timestamp(datetime.now().strftime('%Y-%m-01')))
forecast_dates = pd.date_range(start=forecast_start_date, periods=forecast_steps, freq='MS')

forecast_A_plus = results_A_plus.get_forecast(steps=forecast_steps)
forecast_A_minus = results_A_minus.get_forecast(steps=forecast_steps)

forecast_A_plus_df = forecast_A_plus.summary_frame()
forecast_A_minus_df = forecast_A_minus.summary_frame()

forecast_A_plus_df.index = forecast_dates
forecast_A_minus_df.index = forecast_dates

# Save forecast data to CSV
forecast_A_plus_df.to_csv('C:/XAMPP/htdocs/Serving Hearts/SARIMA/forecast_file_A_plus_sarima.csv', columns=['mean', 'mean_ci_lower', 'mean_ci_upper'])
forecast_A_minus_df.to_csv('C:/XAMPP/htdocs/Serving Hearts/SARIMA/forecast_file_A_minus_sarima.csv', columns=['mean', 'mean_ci_lower', 'mean_ci_upper'])

# Append NaN to observed data to match forecast length
combined_data = combined_data.reindex(combined_data.index.union(forecast_dates))

# Combine observed and forecasted data
combined_data['Combined_A_plus'] = combined_data['Observed_A_plus'].combine_first(combined_data['mean_A_plus'])
combined_data['Combined_A_minus'] = combined_data['Observed_A_minus'].combine_first(combined_data['mean_A_minus'])

# Plotting
plt.figure(figsize=(14, 8))

# Plot observed data
plt.plot(combined_data.index, combined_data['Observed_A_plus'], label='A+ Observed', color='blue', linestyle='-', marker='o')
plt.plot(combined_data.index, combined_data['Observed_A_minus'], label='A- Observed', color='orange', linestyle='-', marker='o')

# Plot forecasted data with confidence intervals
plt.plot(combined_data.index, combined_data['mean_A_plus'], label='A+ Forecast', color='blue', linestyle='--', marker='D', markersize=7)
plt.fill_between(
    combined_data.index, 
    combined_data['mean_ci_lower_A_plus'], 
    combined_data['mean_ci_upper_A_plus'], 
    color='blue', 
    alpha=0.2, 
    label='A+ Forecast Range'
)
plt.plot(combined_data.index, combined_data['mean_A_minus'], label='A- Forecast', color='orange', linestyle='--', marker='D', markersize=7)
plt.fill_between(
    combined_data.index, 
    combined_data['mean_ci_lower_A_minus'], 
    combined_data['mean_ci_upper_A_minus'], 
    color='orange', 
    alpha=0.2, 
    label='A- Forecast Range'
)

# Plot combined (observed + forecast)
plt.plot(combined_data.index, combined_data['Combined_A_plus'], label='A+ Combined (Observed + Forecast)', color='darkblue', linestyle='-', marker='o', alpha=0.6)
plt.plot(combined_data.index, combined_data['Combined_A_minus'], label='A- Combined (Observed + Forecast)', color='darkorange', linestyle='-', marker='o', alpha=0.6)

# Format x-axis to show only month-year
plt.gca().xaxis.set_major_locator(mdates.MonthLocator())
plt.gca().xaxis.set_major_formatter(mdates.DateFormatter('%b %Y'))
plt.xticks(rotation=45)  # Rotate dates for readability

# Add title, labels, grid, and legend
plt.title('Monthly Forecasted and Observed A+ and A- Blood Handover')
plt.xlabel('Date')
plt.ylabel('Blood Handover Count')
plt.grid(visible=True, which='major', linestyle='--', linewidth=0.5)
plt.legend()
plt.tight_layout()

# Save and show the plot
plt.savefig('C:/XAMPP/htdocs/Serving Hearts/Predictions/graphs/a_blood_handover_forecast.png')
plt.show()
