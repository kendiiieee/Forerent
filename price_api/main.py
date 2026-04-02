import joblib
import pandas as pd
import numpy as np
from fastapi import FastAPI, HTTPException
from pydantic import BaseModel, Field
from typing import List, Optional, Dict, Any
from datetime import datetime
import io
import logging
from sklearn.preprocessing import StandardScaler
from sklearn.compose import ColumnTransformer
from sklearn.cluster import AgglomerativeClustering
from sklearn.linear_model import LinearRegression
from sklearn.metrics import silhouette_score, r2_score, mean_absolute_error, mean_absolute_percentage_error
from io import StringIO

# Configure logging
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

app = FastAPI()

# Global variables for model components
model_pipeline = None
cluster_pipeline = None
prediction_pipeline = None
feature_columns = None
model_type = "unknown"

# Maintenance forecast model components
maintenance_model = None
maintenance_scaler = None
maintenance_cluster_labels = None
maintenance_feature_columns = None

# --- 1. Load Price Prediction Model ---
@app.on_event("startup")
async def load_model():
    global model_pipeline, cluster_pipeline, prediction_pipeline, feature_columns, model_type
    
    try:
        model_data = joblib.load("dorm_price_model.joblib")
        
        # Check what type of model we have
        if isinstance(model_data, dict):
            # New format with metadata - extract the actual model
            if 'model' in model_data:
                # The actual pipeline is under 'model' key
                model_pipeline = model_data['model']
                model_type = model_data.get('model_type', 'Unknown')
                feature_columns = model_data.get('feature_columns', [])
                logger.info("Dorm price model loaded successfully (metadata format)")
                logger.info(f"Model type: {model_type}")
                
            elif 'prediction_pipeline' in model_data and 'cluster_pipeline' in model_data:
                # Format with separate pipelines
                cluster_pipeline = model_data['cluster_pipeline']
                prediction_pipeline = model_data['prediction_pipeline']
                feature_columns = model_data['feature_columns']
                model_pipeline = None
                model_type = "separate_pipelines"
                logger.info("Dorm price model loaded successfully (separate pipelines)")
                
            else:
                # Unknown dictionary format - try to extract any pipeline
                for key, value in model_data.items():
                    if hasattr(value, 'predict'):
                        model_pipeline = value
                        model_type = f"extracted_from_{key}"
                        logger.info(f"Dorm price model loaded from key: {key}")
                        break
                else:
                    logger.error("Unknown model dictionary format")
                    return
        else:
            # Old format - direct pipeline
            model_pipeline = model_data
            model_type = "direct_pipeline"
            logger.info("Dorm price model loaded successfully (direct pipeline)")
            
        logger.info(f"✅ Model loaded successfully. Type: {model_type}")
        
    except FileNotFoundError:
        logger.error("Error: dorm_price_model.joblib not found!")
    except Exception as e:
        logger.error(f"Error loading model: {e}")

# Columns for price prediction
ALL_COLUMNS = [
    'Living Area (sqft)', 'Floor', 'Bed type', 'Room capacity', 'Furnishing',
    'Free_Wifi', 'Hot_Cold_Shower', 'Electric_Fan', 'Water_Kettle', 
    'Closet_Cabinet', 'Housekeeping', 'Refrigerator', 'Microwave', 
    'Rice_Cooker', 'Dining_Table', 'Utility_Subsidy', 'AC_Unit', 
    'Induction_Cooker', 'Washing_Machine', 'Access_Pool', 'Access_Gym'
]

# --- 2. Define Input Data Models ---
class UnitFeatures(BaseModel):
    Living_Area: float = Field(alias='Living Area (sqft)')
    Floor: int
    Bed_type: str = Field(alias='Bed type')
    Room_capacity: int = Field(alias='Room capacity')
    Furnishing: str
    
    # All 16 Amenities
    Free_Wifi: int
    Hot_Cold_Shower: int
    Electric_Fan: int
    Water_Kettle: int
    Closet_Cabinet: int
    Housekeeping: int
    Refrigerator: int
    Microwave: int
    Rice_Cooker: int
    Dining_Table: int
    Utility_Subsidy: int
    AC_Unit: int
    Induction_Cooker: int
    Washing_Machine: int
    Access_Pool: int
    Access_Gym: int

class ForecastRequest(BaseModel):
    csv_data: str
    year: int

class MonthlyForecast(BaseModel):
    year: int
    month: int
    month_name: str
    forecasted_revenue: float

class ForecastResponse(BaseModel):
    success: bool
    forecast_year: int
    monthly_forecasts: List[MonthlyForecast]
    total_annual_revenue: float
    total_remaining_revenue: float
    average_monthly_revenue: float
    data_points_used: int
    
# Maintenance Forecast Models
class MaintenanceForecastRequest(BaseModel):
    csv_data: str
    year: int

class MaintenanceMonthlyForecast(BaseModel):
    year: int
    month: int
    month_name: str
    forecasted_cost: float
    maintenance_count_estimate: int
    urgency_estimate: float
    seasonal_factor: float

class MaintenanceScheduleItem(BaseModel):
    month_name: str
    category: str
    priority: str
    estimated_cost: float
    reason: str
    recommended_action: str

class MaintenanceForecastResponse(BaseModel):
    success: bool
    forecast_year: int
    monthly_forecasts: List[MaintenanceMonthlyForecast]
    maintenance_schedule: List[MaintenanceScheduleItem]
    total_annual_cost: float
    total_remaining_cost: float
    average_monthly_cost: float
    data_points_used: int
    model_performance: Dict[str, float]

def preprocess_input_data(input_data: Dict[str, Any]) -> pd.DataFrame:
    """Preprocess input data to match model expectations"""
    # Convert to integer for amenities that are already int
    processed_data = {}
    for key, value in input_data.items():
        if isinstance(value, bool):
            processed_data[key] = 1 if value else 0
        else:
            processed_data[key] = value
    
    # Create DataFrame
    input_df = pd.DataFrame([processed_data])
    
    # Handle column name mappings
    column_mapping = {
        'Living_Area': 'Living Area (sqft)',
        'Bed_type': 'Bed type', 
    }
    input_df = input_df.rename(columns=column_mapping)
    
    return input_df

def predict_with_cluster_pipeline(input_df: pd.DataFrame) -> float:
    """Make prediction using separate cluster and prediction pipelines"""
    try:
        # Remove 'Cluster' from input data since it will be added by clustering
        input_data_without_cluster = {k: v for k, v in input_df.iloc[0].items() if k != 'Cluster'}
        
        # Convert to DataFrame with correct column order
        input_df_for_cluster = pd.DataFrame([input_data_without_cluster], columns=feature_columns)
        
        # Perform clustering
        cluster_labels = cluster_pipeline.predict(input_df_for_cluster)
        input_df_with_cluster = input_df_for_cluster.copy()
        input_df_with_cluster['Cluster'] = cluster_labels.astype(str)
        
        # Make the prediction
        prediction = prediction_pipeline.predict(input_df_with_cluster)
        return float(prediction[0])
        
    except Exception as e:
        logger.error(f"Cluster pipeline prediction error: {e}")
        raise

def predict_with_single_pipeline(input_df: pd.DataFrame) -> float:
    """Make prediction using single pipeline"""
    try:
        # Try without cluster first
        try:
            prediction = model_pipeline.predict(input_df)
            return float(prediction[0])
        except Exception as cluster_error:
            logger.info(f"First attempt failed, trying with cluster field: {cluster_error}")
            # Add cluster field and try again
            input_df_with_cluster = input_df.copy()
            input_df_with_cluster['Cluster'] = '0'  # Default cluster
            prediction = model_pipeline.predict(input_df_with_cluster)
            return float(prediction[0])
            
    except Exception as e:
        logger.error(f"Single pipeline prediction error: {e}")
        raise

# --- 3. Price Prediction Endpoint ---
@app.post("/predict")
def predict_price(features: UnitFeatures):
    try:
        # Check if model is loaded
        if model_pipeline is None and cluster_pipeline is None:
            raise HTTPException(status_code=503, detail="Model not loaded")
        
        # 1. Convert input from Laravel to a Python dictionary
        input_data = features.dict(by_alias=True)
        
        # 2. Preprocess input data
        input_df = preprocess_input_data(input_data)
        
        # 3. Make prediction based on model type
        if cluster_pipeline is not None and prediction_pipeline is not None:
            # Use separate pipelines approach
            prediction_value = predict_with_cluster_pipeline(input_df)
        else:
            # Use single pipeline approach
            prediction_value = predict_with_single_pipeline(input_df)
        
        # Format to exactly 2 decimal places
        formatted_price = round(prediction_value, 2)
        
        logger.info(f"Price prediction made: ₱{formatted_price:.2f}")
        return {"predicted_price": formatted_price}
        
    except Exception as e:
        logger.error(f"Price prediction error: {str(e)}")
        raise HTTPException(status_code=500, detail=str(e))

# Health check endpoint
@app.get("/")
def health_check():
    model_status = "loaded" if (model_pipeline is not None or cluster_pipeline is not None) else "not loaded"
    return {
        "status": "healthy", 
        "service": "price_api",
        "model_status": model_status,
        "model_type": model_type
    }

# Model info endpoint
@app.get("/model-info")
def model_info():
    if model_pipeline is not None or cluster_pipeline is not None:
        return {
            "model_type": model_type,
            "status": "loaded",
            "has_cluster_pipeline": cluster_pipeline is not None,
            "has_prediction_pipeline": prediction_pipeline is not None,
            "has_single_pipeline": model_pipeline is not None
        }
    else:
        return {
            "status": "not_loaded", 
            "error": "Model failed to load"
        }

# Test endpoint for debugging
@app.post("/test-predict")
def test_predict():
    """Test endpoint with sample data"""
    sample_data = {
        "Living Area (sqft)": 120.0,
        "Floor": 3,
        "Bed type": "Single",
        "Room capacity": 2,
        "Furnishing": "Semi-furnished",
        "Free_Wifi": 1,
        "Hot_Cold_Shower": 1,
        "Electric_Fan": 1,
        "Water_Kettle": 1,
        "Closet_Cabinet": 1,
        "Housekeeping": 0,
        "Refrigerator": 0,
        "Microwave": 0,
        "Rice_Cooker": 1,
        "Dining_Table": 0,
        "Utility_Subsidy": 0,
        "AC_Unit": 0,
        "Induction_Cooker": 0,
        "Washing_Machine": 0,
        "Access_Pool": 0,
        "Access_Gym": 0,
    }
    
    try:
        # Test the preprocessing
        test_df = preprocess_input_data(sample_data)
        logger.info(f"Test DataFrame columns: {list(test_df.columns)}")
        logger.info(f"Test DataFrame shape: {test_df.shape}")
        
        return {
            "success": True,
            "columns": list(test_df.columns),
            "data_sample": test_df.iloc[0].to_dict()
        }
    except Exception as e:
        logger.error(f"Test error: {e}")
        return {"success": False, "error": str(e)}

# -----------------------------
# CORRECTED Data Preprocessing
# -----------------------------
def create_features(df):
    """Create time series features from the processed monthly data"""
    df = df.copy()

    # Check if we have the date column (it might be named differently after preprocessing)
    date_col = None
    for col in df.columns:
        if 'date' in col.lower() or col == 'date_column':
            date_col = col
            break
    
    if date_col is None:
        raise KeyError("No date column found in the data. Available columns: " + str(list(df.columns)))

    # Month and quarter from the date column
    df['month'] = df[date_col].dt.month
    df['quarter'] = df[date_col].dt.quarter

    # Cyclical month features
    df['month_sin'] = np.sin(2 * np.pi * df['month'] / 12)
    df['month_cos'] = np.cos(2 * np.pi * df['month'] / 12)

    # Lag features with proper handling of missing values
    df['revenue_lag1'] = df['monthly_net_revenue'].shift(1)
    df['revenue_lag2'] = df['monthly_net_revenue'].shift(2)
    df['revenue_lag3'] = df['monthly_net_revenue'].shift(3)

    # Rolling features
    df['revenue_rolling_mean_3'] = df['monthly_net_revenue'].rolling(window=3, min_periods=1).mean()
    df['revenue_rolling_std_3'] = df['monthly_net_revenue'].rolling(window=3, min_periods=1).std().fillna(0)

    # Fill NaN values from lag features
    df['revenue_lag1'] = df['revenue_lag1'].fillna(method='bfill').fillna(method='ffill')
    df['revenue_lag2'] = df['revenue_lag2'].fillna(method='bfill').fillna(method='ffill')
    df['revenue_lag3'] = df['revenue_lag3'].fillna(method='bfill').fillna(method='ffill')

    # Placeholder transaction count if not in CSV
    if 'transaction_count' not in df.columns:
        df['transaction_count'] = df.get('maintenance_count', 0)  # Use maintenance_count if available, else 0

    return df

def load_and_preprocess_data(csv_data: str):
    """Load and preprocess CSV data for revenue forecasting"""
    df = pd.read_csv(StringIO(csv_data))
    
    logger.info(f"Original CSV columns: {list(df.columns)}")
    logger.info(f"Original CSV shape: {df.shape}")

    # Find the date column - more flexible matching
    date_col = None
    for col in df.columns:
        if any(date_keyword in col.lower() for date_keyword in ['date', 'time', 'period', 'month', 'year']):
            date_col = col
            break
    
    if date_col is None:
        # If no date column found, use the first column that looks like a date
        for col in df.columns:
            try:
                pd.to_datetime(df[col].head(), errors='raise')
                date_col = col
                break
            except:
                continue
    
    if date_col is None:
        raise Exception("No date column found in CSV. Available columns: " + str(list(df.columns)))

    # Convert to datetime
    df[date_col] = pd.to_datetime(df[date_col], errors='coerce')
    df = df.dropna(subset=[date_col])

    # Find revenue/amount column - more flexible matching
    amount_col = None
    for col in df.columns:
        if any(amount_keyword in col.lower() for amount_keyword in ['amount', 'revenue', 'cost', 'price', 'value', 'net']):
            if col != date_col:
                amount_col = col
                break

    if amount_col is None:
        # Use first numeric column that's not the date
        numeric_cols = df.select_dtypes(include=[np.number]).columns.tolist()
        if numeric_cols:
            amount_col = numeric_cols[0]
        else:
            raise Exception("No numeric revenue column found in CSV.")

    logger.info(f"Using date column: {date_col}, amount column: {amount_col}")

    # Group by month and calculate gross inflow revenue only
    df['year'] = df[date_col].dt.year
    df['month'] = df[date_col].dt.month

    # If transaction type is available, only include inflows for gross revenue forecasting.
    if 'transaction_type' in df.columns:
        txn_type = df['transaction_type'].astype(str).str.strip().str.upper()
        inflow_df = df[txn_type.isin(['CREDIT', 'INFLOW'])].copy()

        if inflow_df.empty:
            raise Exception("No inflow transactions found for revenue forecasting")

        count_col = 'transaction_id' if 'transaction_id' in inflow_df.columns else inflow_df.columns[0]
        monthly_data = inflow_df.groupby(['year', 'month']).agg({
            amount_col: 'sum',
            count_col: 'count'
        }).reset_index()
        monthly_data.columns = ['year', 'month', 'monthly_net_revenue', 'transaction_count']
    else:
        # Fallback: keep only positive values as inflows when transaction type is unavailable.
        inflow_df = df[df[amount_col] > 0].copy()
        if inflow_df.empty:
            raise Exception("No positive inflow values found for revenue forecasting")

        monthly_data = inflow_df.groupby(['year', 'month']).agg({
            amount_col: 'sum',
            inflow_df.columns[0]: 'count'  # Use first column for count
        }).reset_index()
        monthly_data.columns = ['year', 'month', 'monthly_net_revenue', 'transaction_count']

    # Create proper date column for sorting
    monthly_data['date_column'] = pd.to_datetime(
        monthly_data['year'].astype(str) + '-' + 
        monthly_data['month'].astype(str) + '-01'
    )

    monthly_data = monthly_data.sort_values('date_column').reset_index(drop=True)
    
    logger.info(f"Processed monthly data shape: {monthly_data.shape}")
    logger.info(f"Processed columns: {list(monthly_data.columns)}")
    
    return monthly_data

def train_forecast_model(X, y, cluster_range=(2, 6)):
    """
    Train forecasting model with automatic cluster selection - IMPROVED
    """
    scaler = StandardScaler()
    X_scaled = scaler.fit_transform(X)

    # Find best cluster count
    best_score = -1
    best_n = cluster_range[0]
    best_labels = None

    for n_clusters in range(cluster_range[0], cluster_range[1] + 1):
        try:
            # Use ward linkage for better cluster separation
            clustering = AgglomerativeClustering(
                n_clusters=n_clusters,
                linkage='ward'  # Better for time series
            )
            labels = clustering.fit_predict(X_scaled)
            
            # Only calculate silhouette if we have at least 2 clusters
            if len(np.unique(labels)) > 1:
                score = silhouette_score(X_scaled, labels)
                logger.info(f"Testing {n_clusters} clusters: silhouette={score:.4f}, unique_clusters={len(np.unique(labels))}")
                
                if score > best_score:
                    best_score = score
                    best_n = n_clusters
                    best_labels = labels
        except Exception as e:
            logger.warning(f"Clustering failed for {n_clusters} clusters: {e}")
            continue

    logger.info(f"✓ Selected {best_n} clusters with silhouette score: {best_score:.4f}")

    # Use the best clustering
    final_clustering = AgglomerativeClustering(n_clusters=best_n, linkage='ward')
    cluster_labels = final_clustering.fit_predict(X_scaled)
    
    # Log cluster distribution
    unique, counts = np.unique(cluster_labels, return_counts=True)
    logger.info(f"Cluster distribution: {dict(zip(unique, counts))}")

    # Train one linear regression model per cluster
    cluster_models = {}
    for cluster_id in range(best_n):
        cluster_indices = cluster_labels == cluster_id
        X_cluster = X.iloc[cluster_indices]
        y_cluster = y.iloc[cluster_indices]

        if len(X_cluster) >= 2:
            model = LinearRegression()
            model.fit(X_cluster, y_cluster)
            cluster_models[cluster_id] = model
            
            logger.info(f"Cluster {cluster_id}: {len(X_cluster)} samples, "
                       f"avg=₱{y_cluster.mean():,.2f}, "
                       f"std=₱{y_cluster.std():,.2f}, "
                       f"min=₱{y_cluster.min():,.2f}, "
                       f"max=₱{y_cluster.max():,.2f}")

    return {
        "models": cluster_models,
        "scaler": scaler,
        "cluster_labels": cluster_labels,
        "best_n_clusters": best_n,
        "X_scaled": X_scaled  # Keep this for better cluster assignment
    }

def forecast_all_months(cluster_models, scaler, cluster_labels, monthly_data, target_year, feature_columns):
    """
    Generate forecasts with better variation handling
    """
    logger.info("Starting revenue forecast with improved variation...")
    forecasts = []
    
    # Get historical statistics
    historical_mean = monthly_data['monthly_net_revenue'].mean()
    historical_std = monthly_data['monthly_net_revenue'].std()
    historical_min = monthly_data['monthly_net_revenue'].min()
    historical_max = monthly_data['monthly_net_revenue'].max()

    logger.info(f"Historical stats - Mean: ₱{historical_mean:,.2f}, "
                f"Std: ₱{historical_std:,.2f}, "
                f"Range: ₱{historical_min:,.2f} to ₱{historical_max:,.2f}")

    # Get monthly patterns from history
    monthly_patterns = monthly_data.groupby('month').agg({
        'monthly_net_revenue': ['mean', 'std', 'count'],
        'transaction_count': 'mean'
    }).reset_index()
    
    monthly_patterns.columns = ['month', 'revenue_mean', 'revenue_std', 'count', 'transaction_count']
    logger.info(f"Monthly patterns:\n{monthly_patterns}")

    # Initialize from last known data
    last_row = monthly_data.iloc[-1].copy()
    
    # Get recent history for better context
    recent_data = monthly_data.tail(12)  # Last year of data
    
    current_lags = {
        'revenue_lag1': last_row['monthly_net_revenue'], 
        'revenue_lag2': last_row.get('revenue_lag1', recent_data['monthly_net_revenue'].iloc[-2] if len(recent_data) >= 2 else historical_mean),
        'revenue_lag3': last_row.get('revenue_lag2', recent_data['monthly_net_revenue'].iloc[-3] if len(recent_data) >= 3 else historical_mean),
    }
    
    recent_revenues = list(monthly_data['monthly_net_revenue'].iloc[-6:])  # Last 6 months
    
    # Prepare for dynamic cluster assignment
    from sklearn.neighbors import NearestNeighbors
    X_historical = monthly_data[feature_columns]
    X_historical_scaled = scaler.transform(X_historical)
    
    nn = NearestNeighbors(n_neighbors=3)  # Use 3 nearest neighbors for smoother assignment
    nn.fit(X_historical_scaled)

    for month in range(1, 13):
        # Get historical pattern for this specific month
        month_pattern = monthly_patterns[monthly_patterns['month'] == month]
        
        if not month_pattern.empty:
            expected_revenue = month_pattern['revenue_mean'].iloc[0]
            expected_std = month_pattern['revenue_std'].iloc[0]
            expected_transactions = month_pattern['transaction_count'].iloc[0]
        else:
            expected_revenue = historical_mean
            expected_std = historical_std
            expected_transactions = monthly_data['transaction_count'].mean()
        
        # Calculate rolling features
        rolling_mean = np.mean(recent_revenues[-3:]) if len(recent_revenues) >= 3 else expected_revenue
        rolling_std = np.std(recent_revenues[-3:]) if len(recent_revenues) >= 3 else expected_std
        
        # Create features
        current_features = {
            'month': month,
            'quarter': (month - 1) // 3 + 1,
            'month_sin': np.sin(2 * np.pi * month / 12),
            'month_cos': np.cos(2 * np.pi * month / 12),
            'revenue_lag1': current_lags['revenue_lag1'],
            'revenue_lag2': current_lags['revenue_lag2'],
            'revenue_lag3': current_lags['revenue_lag3'],
            'revenue_rolling_mean_3': rolling_mean,
            'revenue_rolling_std_3': rolling_std if np.isfinite(rolling_std) else expected_std,
            'transaction_count': expected_transactions
        }
        
        current_df = pd.DataFrame([current_features])[feature_columns]
        current_scaled = scaler.transform(current_df)
        
        # Find nearest neighbors and take majority vote on cluster
        distances, indices = nn.kneighbors(current_scaled)
        neighbor_clusters = cluster_labels[indices[0]]
        predicted_cluster = np.bincount(neighbor_clusters).argmax()  # Most common cluster
        
        # Get model and predict
        model = cluster_models.get(predicted_cluster)
        
        if model is None and cluster_models:
            model = list(cluster_models.values())[0]
        
        if model is None:
            forecast_revenue = expected_revenue
        else:
            # Make prediction
            raw_prediction = model.predict(current_df)[0]
            
            # Apply soft bounds based on historical patterns for this month
            # Allow deviation but pull towards historical average
            weight_history = 0.3  # 30% weight to historical pattern
            weight_model = 0.7    # 70% weight to model prediction
            
            forecast_revenue = (weight_model * raw_prediction + 
                              weight_history * expected_revenue)
        
        # Apply reasonable global bounds
        min_bound = max(historical_min * 0.5, expected_revenue * 0.5)
        max_bound = min(historical_max * 1.5, expected_revenue * 1.5)
        forecast_revenue = np.clip(forecast_revenue, min_bound, max_bound)
        
        month_name = datetime(target_year, month, 1).strftime('%B')
        logger.info(f"{month_name}: Cluster {predicted_cluster}, "
                   f"Predicted: ₱{raw_prediction:,.2f}, "
                   f"Expected: ₱{expected_revenue:,.2f}, "
                   f"Final: ₱{forecast_revenue:,.2f}")

        forecasts.append({
            'year': target_year,
            'month': month,
            'month_name': month_name,
            'forecasted_revenue': float(forecast_revenue)
        })

        # Update state for next iteration
        current_lags['revenue_lag3'] = current_lags['revenue_lag2']
        current_lags['revenue_lag2'] = current_lags['revenue_lag1']
        current_lags['revenue_lag1'] = forecast_revenue
        
        recent_revenues.append(forecast_revenue)
        recent_revenues = recent_revenues[-6:]

    return forecasts

def generate_revenue_forecast(csv_data: str, target_year: int):
    """Main function to generate revenue forecast"""
    try:
        monthly_data = load_and_preprocess_data(csv_data)
        
        logger.info(f"Monthly data loaded: {len(monthly_data)} records")
        logger.info(f"Date range: {monthly_data['date_column'].min()} to {monthly_data['date_column'].max()}")

        # Filter to only COMPLETE historical months (not current/future incomplete months)
        current_date = datetime.now()
        cutoff_date = datetime(current_date.year, current_date.month, 1)
        monthly_data = monthly_data[monthly_data['date_column'] < cutoff_date].copy()
        
        logger.info(f"After filtering incomplete months: {len(monthly_data)} complete historical months")

        if len(monthly_data) < 4:  # Reduced minimum requirement
            raise Exception(f"Insufficient historical data for forecasting. Need at least 4 months, got {len(monthly_data)}")

        monthly_data_clean = create_features(monthly_data)
        
        logger.info(f"After feature engineering: {len(monthly_data_clean)} records")

        if len(monthly_data_clean) < 3:
            raise Exception("Not enough data after feature engineering.")

        feature_columns = [
            'month', 'quarter', 'month_sin', 'month_cos',
            'revenue_lag1', 'revenue_lag2', 'revenue_lag3',
            'revenue_rolling_mean_3', 'revenue_rolling_std_3',
            'transaction_count'
        ]

        X = monthly_data_clean[feature_columns]
        y = monthly_data_clean['monthly_net_revenue']

        logger.info(f"Training data - X: {X.shape}, y: {y.shape}")

        trained = train_forecast_model(X, y, cluster_range=(2, min(6, len(X)-1)))
        cluster_models = trained["models"]
        scaler = trained["scaler"]
        cluster_labels = trained["cluster_labels"]

        forecasts = forecast_all_months(
            cluster_models, scaler, cluster_labels,
            monthly_data_clean, target_year, feature_columns
        )

        total_annual = sum(item['forecasted_revenue'] for item in forecasts)
        current_month = datetime.now().month
        
        if target_year == datetime.now().year:
            remaining_forecasts = [item for item in forecasts if item['month'] >= current_month]
            total_remaining = sum(item['forecasted_revenue'] for item in remaining_forecasts)
        else:
            total_remaining = total_annual

        return {
            'success': True,
            'forecast_year': target_year,
            'monthly_forecasts': forecasts,
            'total_annual_revenue': total_annual,
            'total_remaining_revenue': total_remaining,
            'average_monthly_revenue': total_annual / 12,
            'data_points_used': len(monthly_data_clean)
        }
        
    except Exception as e:
        logger.error(f"Error in generate_revenue_forecast: {str(e)}")
        raise

# ---------------------------
# API endpoint
# ---------------------------
@app.post("/api/forecast/revenue")
def forecast_revenue(request: ForecastRequest):
    try:
        logger.info(f"Received forecast request for year: {request.year}")
        result = generate_revenue_forecast(request.csv_data, request.year)
        logger.info(f"Forecast completed successfully: {len(result['monthly_forecasts'])} months")
        return result
    except Exception as e:
        logger.error(f"Error in forecast_revenue: {str(e)}")
        import traceback
        logger.error(f"Traceback: {traceback.format_exc()}")
        raise HTTPException(status_code=500, detail=str(e))


# --- Maintenance Forecast Functions ---
def load_and_preprocess_maintenance_data(csv_data: str):
    """Load and preprocess maintenance data for forecasting"""
    df = pd.read_csv(StringIO(csv_data))
    
    logger.info(f"Raw maintenance data columns: {list(df.columns)}")
    logger.info(f"Raw maintenance data shape: {df.shape}") # This should now be (267, 4)

    # Find date column (will find 'date' from our PHP query)
    date_col = None
    for col in df.columns:
        if any(date_keyword in col.lower() for date_keyword in ['date', 'log_date', 'completion_date', 'time']):
            date_col = col
            break
    
    if date_col is None:
        raise Exception("No date column (e.g. 'date', 'completion_date') found in maintenance data")

    # Convert to datetime
    df[date_col] = pd.to_datetime(df[date_col], errors='coerce')
    df = df.dropna(subset=[date_col])

    # Find cost column (will find 'cost')
    cost_col = None
    for col in df.columns:
        if any(cost_keyword in col.lower() for cost_keyword in ['cost', 'amount', 'price']):
            cost_col = col
            break

    if cost_col is None:
        raise Exception("No cost column (e.g. 'cost') found in maintenance data")

    # Convert cost column to numeric
    df[cost_col] = pd.to_numeric(df[cost_col], errors='coerce')
    df = df.dropna(subset=[cost_col])
    logger.info(f"Records remaining after conversion: {len(df)}")

    # Group by month and calculate maintenance metrics
    df['year'] = df[date_col].dt.year
    df['month'] = df[date_col].dt.month

    # Calculate urgency score
    # Calculate urgency score
    def calculate_urgency_score(urgency):
        urgency_mapping = {'Level 1': 4, 'Level 2': 3, 'Level 3': 2, 'Level 4': 1}
        return urgency_mapping.get(urgency, 2) # Default to 2 if 'urgency' is weird

    # --- THIS IS THE FIX ---
    # We must add 'category' to the aggregation
    
    # Get the most common category for a group (or 'Unknown')
    def get_most_common_category(x):
        return x.mode().iloc[0] if not x.mode().empty else 'Unknown'

    if 'urgency' in df.columns:
        df['urgency_score'] = df['urgency'].apply(calculate_urgency_score)
        agg_dict = {
            'monthly_maintenance_cost': (cost_col, 'sum'),
            'maintenance_count': ('log_id', 'count'),
            'urgency_score': ('urgency_score', 'sum'),
            'category': ('category', get_most_common_category) # <-- ADDED THIS
        }
    else:
        # Fallback if 'urgency' column wasn't sent
        agg_dict = {
            'monthly_maintenance_cost': (cost_col, 'sum'),
            'maintenance_count': ('log_id', 'count'),
            'category': ('category', get_most_common_category) # <-- ADDED THIS
        }
    # --- END OF FIX ---

    monthly_data = df.groupby(['year', 'month'], as_index=False).agg(**agg_dict)

    # Rename columns to match the 'train' function
    monthly_data.rename(columns={
        cost_col: 'monthly_maintenance_cost',
        'log_id': 'maintenance_count'
    }, inplace=True)
    
    if 'urgency_score' not in monthly_data.columns:
        monthly_data['urgency_score'] = 0

    monthly_data['date_column'] = pd.to_datetime(
        monthly_data['year'].astype(str) + '-' + 
        monthly_data['month'].astype(str) + '-01'
    )

    monthly_data = monthly_data.sort_values('date_column').reset_index(drop=True)
    
    logger.info(f"Processed maintenance monthly data shape: {monthly_data.shape}") # This should now be (36, 6)
    logger.info(f"Date range: {monthly_data['date_column'].min()} to {monthly_data['date_column'].max()}")
    
    return monthly_data

def create_maintenance_features(df):
    """Create time series features for maintenance forecasting"""
    df = df.copy()

    # Basic time features
    df['month_sin'] = np.sin(2 * np.pi * df['month'] / 12)
    df['month_cos'] = np.cos(2 * np.pi * df['month'] / 12)
    df['quarter'] = (df['month'] - 1) // 3 + 1

    # Seasonal factors
    df['winter_factor'] = df['month'].isin([12, 1, 2]).astype(int)
    df['summer_factor'] = df['month'].isin([6, 7, 8]).astype(int)

    # Lag features
    df['cost_lag1'] = df['monthly_maintenance_cost'].shift(1)
    df['cost_lag2'] = df['monthly_maintenance_cost'].shift(2)
    df['cost_lag3'] = df['monthly_maintenance_cost'].shift(3)

    # Rolling statistics
    df['cost_rolling_mean_3'] = df['monthly_maintenance_cost'].rolling(3, min_periods=1).mean()
    df['cost_rolling_std_3'] = df['monthly_maintenance_cost'].rolling(3, min_periods=1).std().fillna(0)

    # Count and urgency rolling features
    df['count_rolling_mean_3'] = df['maintenance_count'].rolling(3, min_periods=1).mean()
    df['urgency_rolling_mean_3'] = df['urgency_score'].rolling(3, min_periods=1).mean()

    # Fill NaN values
    for col in ['cost_lag1', 'cost_lag2', 'cost_lag3']:
        df[col] = df[col].fillna(method='bfill').fillna(method='ffill')

    return df

def train_maintenance_model(df_monthly, forecast_year, cluster_range):
    """
    Train the maintenance forecasting model.
    """
    logger.info("Starting maintenance model training...")

    # 1. Feature Engineering (This was create_maintenance_features)
    # This must be done on the full df_monthly
    df_monthly['cost_lag_1'] = df_monthly['monthly_maintenance_cost'].shift(1)
    df_monthly['cost_rolling_3'] = df_monthly['monthly_maintenance_cost'].rolling(window=3).mean()
    df_monthly['cost_lag_12'] = df_monthly['monthly_maintenance_cost'].shift(12)
    df_monthly['month_sin'] = np.sin(2 * np.pi * df_monthly['month']/12)
    df_monthly['month_cos'] = np.cos(2 * np.pi * df_monthly['month']/12)
    
    # Drop rows with NaN values created by shift/rolling
    monthly_data_clean = df_monthly.dropna().copy()
    
    if len(monthly_data_clean) < 4:
        raise ValueError(f"Insufficient maintenance data for forecasting. Need at least 4 months, got {len(monthly_data_clean)}")

    # 2. Clustering
    # Select features for clustering
    cluster_features = ['monthly_maintenance_cost', 'maintenance_count', 'urgency_score', 'cost_lag_1', 'cost_rolling_3']
    X_cluster = monthly_data_clean[cluster_features]
    
    scaler = StandardScaler()
    X_cluster_scaled = scaler.fit_transform(X_cluster)
    
    # Find best number of clusters
    best_n_clusters = 1
    best_silhouette = -1
    
    # Use the cluster_range
    min_clusters, max_clusters = cluster_range
    if max_clusters <= min_clusters:
        max_clusters = min_clusters + 1
    
    for n in range(min_clusters, max_clusters):
        if n >= len(X_cluster_scaled): # Cannot have more clusters than samples
            break
            
        clusterer = AgglomerativeClustering(n_clusters=n)
        labels = clusterer.fit_predict(X_cluster_scaled)
        
        if len(set(labels)) > 1: # silhouette_score requires at least 2 labels
            score = silhouette_score(X_cluster_scaled, labels)
            if score > best_silhouette:
                best_silhouette = score
                best_n_clusters = n

    logger.info(f"Selected {best_n_clusters} clusters with silhouette score: {best_silhouette:.4f}")

    # Add cluster labels to data
    if best_n_clusters > 1:
        clusterer = AgglomerativeClustering(n_clusters=best_n_clusters)
        monthly_data_clean['cluster'] = clusterer.fit_predict(X_cluster_scaled)
    else:
        monthly_data_clean['cluster'] = 0

    # 3. Model Training
    # Define features (X) and target (y)
    features = [col for col in monthly_data_clean.columns if col not in ['date_column', 'year', 'month', 'monthly_maintenance_cost', 'category']]
    X = monthly_data_clean[features]
    y = monthly_data_clean['monthly_maintenance_cost']
    
    # Scale all features (including cluster)
    scaler = StandardScaler()
    X_scaled = scaler.fit_transform(X)

    # 4. Model Evaluation
    # Initialize metrics to default values
    r2, mae, mape = 0.0, 0.0, 0.0
    models = {} # Dictionary to hold models for each cluster

    if best_n_clusters == 1:
        # Train one model on all data
        model = LinearRegression()
        model.fit(X_scaled, y)
        
        y_pred_all = model.predict(X_scaled)
        r2 = r2_score(y, y_pred_all)
        mae = mean_absolute_error(y, y_pred_all)
        mape = mean_absolute_percentage_error(y, y_pred_all)
        
        models[0] = model # Store the single model
    else:
        # Train a model for each cluster
        y_preds = []
        y_trues = []
        
        for cluster_id in range(best_n_clusters):
            # Find data for this cluster
            cluster_mask = (X['cluster'] == cluster_id)
            X_cluster = X_scaled[cluster_mask]
            y_cluster = y[cluster_mask]
            
            if len(y_cluster) > 0:
                model = LinearRegression()
                model.fit(X_cluster, y_cluster)
                models[cluster_id] = model
                
                # Predict on this cluster's data
                y_pred_cluster = model.predict(X_cluster)
                y_preds.append(y_pred_cluster)
                y_trues.append(y_cluster)
            
        # Calculate overall metrics
        y_pred_all = np.concatenate(y_preds)
        y_true_all = np.concatenate(y_trues)
        
        r2 = r2_score(y_true_all, y_pred_all)
        mae = mean_absolute_error(y_true_all, y_pred_all)
        mape = mean_absolute_percentage_error(y_true_all, y_pred_all)

    # THIS IS THE MOVED LOGGER LINE
    logger.info(f"Model trained. R2: {r2:.4f}, MAE: {mae:.2f}")

    return {
        "models": models, # We only return the dictionary of models
        "scaler": scaler,
        "features": features,
        "monthly_data_clean": monthly_data_clean, # Pass the cleaned data
        "cluster_labels": monthly_data_clean['cluster'],
        "best_n_clusters": best_n_clusters,
        "silhouette_score": best_silhouette,
        "performance": {
            "r2": r2,
            "mae": mae,
            "mape": mape
        }
    }

def generate_maintenance_schedule(forecast_results, limit=5):
    """Generate maintenance schedule based on forecasts (limited to specified number)"""
    schedule = []
    
    maintenance_categories = {
        'Plumbing': {'months': [1, 2, 12], 'cost_factor': 0.25, 'priority': 'High', 'reason': 'Winter peak season - pipe maintenance'},
        'Electrical': {'months': [6, 7, 8], 'cost_factor': 0.20, 'priority': 'High', 'reason': 'Summer peak season - AC electrical load'},
        'Structural': {'months': [3, 4, 9, 10], 'cost_factor': 0.30, 'priority': 'Medium', 'reason': 'Moderate weather - ideal for structural work'},
        'Appliance': {'months': [5, 11], 'cost_factor': 0.15, 'priority': 'Medium', 'reason': 'Shoulder seasons - preventive maintenance'},
        'Pest Control': {'months': [3, 6, 9, 12], 'cost_factor': 0.10, 'priority': 'Medium', 'reason': 'Quarterly preventive treatment'}
    }
    
    for forecast in forecast_results:
        month = forecast['month']
        total_cost = forecast['forecasted_cost']
        
        for category, props in maintenance_categories.items():
            if month in props['months']:
                schedule.append({
                    'month_name': forecast['month_name'],
                    'category': category,
                    'priority': props['priority'],
                    'estimated_cost': total_cost * props['cost_factor'],
                    'reason': props['reason'],
                    'recommended_action': f"Schedule {category} inspection and maintenance"
                })
    
    # Limit to top 5 results
    return schedule[:limit]

def forecast_maintenance_months(model, scaler, monthly_data, target_year, feature_columns):
    """Generate maintenance forecasts for all months of target year"""
    forecasts = []
    
    # Use the most recent data point as base
    last_row = monthly_data.iloc[-1].copy()
    
    # Get historical statistics
    historical_mean = monthly_data['monthly_maintenance_cost'].mean()
    historical_std = monthly_data['monthly_maintenance_cost'].std()
    historical_count_mean = monthly_data['maintenance_count'].mean()
    historical_urgency_mean = monthly_data['urgency_score'].mean()

    # Maintenance seasonal patterns
    seasonal_patterns = {
        1: 1.25, 2: 1.15, 3: 1.05, 4: 0.95, 5: 0.90, 6: 1.10,
        7: 1.30, 8: 1.25, 9: 1.05, 10: 0.95, 11: 0.90, 12: 1.20
    }

    for month in range(1, 13):
        seasonal_factor = seasonal_patterns.get(month, 1.0)
        
        # Create features for the target month
        current_features = {
            'month': month,
            'quarter': (month - 1) // 3 + 1,
            'month_sin': np.sin(2 * np.pi * month / 12),
            'month_cos': np.cos(2 * np.pi * month / 12),
            'winter_factor': 1 if month in [12, 1, 2] else 0,
            'summer_factor': 1 if month in [6, 7, 8] else 0,
            'cost_lag1': last_row.get('cost_lag1', historical_mean) * seasonal_factor,
            'cost_lag2': last_row.get('cost_lag2', historical_mean) * seasonal_factor,
            'cost_lag3': last_row.get('cost_lag3', historical_mean) * seasonal_factor,
            'cost_rolling_mean_3': last_row.get('cost_rolling_mean_3', historical_mean) * seasonal_factor,
            'cost_rolling_std_3': last_row.get('cost_rolling_std_3', historical_std),
            'maintenance_count': historical_count_mean * seasonal_factor,
            'count_rolling_mean_3': historical_count_mean * seasonal_factor,
            'urgency_score': historical_urgency_mean * seasonal_factor,
            'urgency_rolling_mean_3': historical_urgency_mean * seasonal_factor
        }

        current_df = pd.DataFrame([current_features])[feature_columns]
        
        # Scale the features
        current_scaled = scaler.transform(current_df)
        
        # Make prediction
        forecast_cost = model.predict(current_scaled)[0]

        # Apply seasonal adjustment and ensure reasonable values
        seasonal_adjusted_cost = forecast_cost * seasonal_factor
        min_reasonable = historical_mean * 0.5
        max_reasonable = historical_mean * 2.0
        final_cost = np.clip(seasonal_adjusted_cost, min_reasonable, max_reasonable)

        forecasts.append({
            'year': target_year,
            'month': month,
            'month_name': datetime(target_year, month, 1).strftime('%B'),
            'forecasted_cost': float(final_cost),
            'maintenance_count_estimate': int(current_features['maintenance_count']),
            'urgency_estimate': float(current_features['urgency_score']),
            'seasonal_factor': float(seasonal_factor)
        })

    return forecasts

def create_forecast_schedule(monthly_data_clean, features, forecast_year):
    """
    Creates a DataFrame for the forecast year with all required features.
    """
    # Create a DataFrame for the 12 months of the forecast year
    forecast_dates = pd.date_range(start=f'{forecast_year}-01-01', periods=12, freq='MS')
    forecast_df = pd.DataFrame(forecast_dates, columns=['date_column'])
    
    forecast_df['year'] = forecast_df['date_column'].dt.year
    forecast_df['month'] = forecast_df['date_column'].dt.month
    
    # --- Re-create all features ---
    
    # --- THIS IS THE FIX ---
    # Create historical averages for each specific month
    monthly_avg_lookup = monthly_data_clean.groupby('month')[[
        'maintenance_count', 
        'urgency_score',
        'cost_lag_12' # Use this as a fallback
    ]].mean().reset_index()
    
    # Merge these month-specific averages into the forecast
    forecast_df = forecast_df.merge(monthly_avg_lookup, on='month', how='left')
    
    # Fill any missing months (e.g., if no historical data for Dec) with the overall mean
    forecast_df['maintenance_count'] = forecast_df['maintenance_count'].fillna(monthly_data_clean['maintenance_count'].mean())
    forecast_df['urgency_score'] = forecast_df['urgency_score'].fillna(monthly_data_clean['urgency_score'].mean())
    # --- END OF FIX ---

    # We need to create lag features based on the *last* known data
    last_known_month = monthly_data_clean.iloc[-1]
    
    # Simple features
    forecast_df['month_sin'] = np.sin(2 * np.pi * forecast_df['month']/12)
    forecast_df['month_cos'] = np.cos(2 * np.pi * forecast_df['month']/12)
    
    # Set default values for features that will be filled
    forecast_df['cost_lag_1'] = 0.0
    forecast_df['cost_rolling_3'] = 0.0
    
    # Fill lag features from the last row of historical data
    forecast_df.loc[0, 'cost_lag_1'] = last_known_month['monthly_maintenance_cost']
    forecast_df.loc[0, 'cost_rolling_3'] = monthly_data_clean['monthly_maintenance_cost'].iloc[-3:].mean()
    
    # Get lag_12 from 12 months ago (if available)
    last_year_match = monthly_data_clean[
        (monthly_data_clean['year'] == forecast_year - 1) &
        (monthly_data_clean['month'] == 1)
    ]
    if not last_year_match.empty:
        forecast_df.loc[0, 'cost_lag_12'] = last_year_match.iloc[0]['monthly_maintenance_cost']
    else:
        # Use the historical average for January
        forecast_df.loc[0, 'cost_lag_12'] = forecast_df.loc[0, 'cost_lag_12'] # Use the value from the lookup

    # Set cluster
    forecast_df['cluster'] = last_known_month['cluster']

    # Return the full forecast dataframe
    return forecast_df

def get_maintenance_schedule(forecast_df, historical_data, top_n=5):
    """
    Analyzes historical data to recommend a maintenance schedule.
    This version finds the most costly and urgent categories.
    """
    if 'category' not in historical_data.columns:
        logger.warning("Cannot generate schedule: 'category' column missing.")
        return []

    # Group by category to find total cost, avg urgency, and count
    category_stats = historical_data.groupby('category').agg({
        'monthly_maintenance_cost': 'sum',
        'urgency_score': 'mean',
        'maintenance_count': 'sum'
    }).reset_index()
    
    # Create a 'priority_score'
    # We weigh cost and urgency
    category_stats['priority_score'] = (
        category_stats['monthly_maintenance_cost'] * category_stats['urgency_score']
    )
    
    # Get the Top 5 most critical categories
    top_categories = category_stats.sort_values('priority_score', ascending=False).head(top_n)

    schedule = []
    
    for _, row in top_categories.iterrows():
        # Find the month this category was *historically* most expensive
        hist_month = historical_data[historical_data['category'] == row['category']].sort_values(
            'monthly_maintenance_cost', ascending=False
        ).iloc[0]
        
        month_name = hist_month['date_column'].strftime('%B')
        
        # Find the corresponding *forecasted* cost for that same month
        forecast_row = forecast_df[forecast_df['month'] == hist_month['month']]
        
        if not forecast_row.empty:
            est_cost = forecast_row.iloc[0]['forecasted_cost']
        else:
            est_cost = 0 # Should not happen

        schedule.append({
            "month_name": month_name,
            "category": row['category'],
            "priority": "High", # They are the top 5
            "estimated_cost": est_cost,
            "reason": f"Historically high cost (Total: ₱{row['monthly_maintenance_cost']:.0f}) & urgency."
        })
        
    return schedule

def generate_maintenance_forecast(csv_data, year):
    try:
        # 1. Load and preprocess data (gets 36 monthly rows)
        monthly_data = load_and_preprocess_maintenance_data(csv_data)
        logger.info(f"Maintenance monthly data loaded: {len(monthly_data)} records")

        if len(monthly_data) < 4:
            raise Exception(f"Insufficient maintenance data for forecasting. Need at least 4 months, got {len(monthly_data)}")

        # 2. Train model 
        # (train_maintenance_model does its *own* feature engineering)
        
        # THIS IS THE FIX: We create the cluster_range first
        cluster_range = (2, min(6, len(monthly_data) - 1)) # -1 because of dropna
        
        # THEN WE CALL train_maintenance_model, passing it the *full* monthly_data
        # This was the bug. The code was passing X and y, which was wrong.
        trained = train_maintenance_model(monthly_data.copy(), year, cluster_range=cluster_range)

        # 3. Get trained components
        models = trained["models"] # This is now a dictionary
        scaler = trained["scaler"]
        features = trained["features"]
        monthly_data_clean = trained["monthly_data_clean"] # Get the cleaned data
        
        # 4. Create the forecast DataFrame
        forecast_df = create_forecast_schedule(
            monthly_data_clean, 
            features, 
            year
        )
        
        # 5. Predict
        forecast_df_scaled = scaler.transform(forecast_df[features])
        
        # Handle per-cluster prediction
        best_n_clusters = trained["best_n_clusters"]
        
        # Get the "cluster" column from the features list
        cluster_col_index = features.index('cluster')
        
        # Predict based on cluster
        all_preds = []
        for i in range(len(forecast_df_scaled)):
            cluster_val = int(forecast_df_scaled[i, cluster_col_index])
            
            # Use the model for that cluster
            if cluster_val in models:
                pred = models[cluster_val].predict([forecast_df_scaled[i]])
                all_preds.append(pred[0])
            else:
                # Fallback: just use cluster 0's model
                pred = models[0].predict([forecast_df_scaled[i]])
                all_preds.append(pred[0])

        forecast_df['forecasted_cost'] = np.maximum(0, all_preds) # Ensure no negative cost

        # 6. Generate Maintenance Schedule
        maintenance_schedule = get_maintenance_schedule(
            forecast_df,
            monthly_data_clean,
            top_n=5
        )

        # 7. Aggregate results
        forecast_df['month_name'] = forecast_df['date_column'].dt.strftime('%B')
        
        # Rename columns to match the Blade file's expectations
        forecast_df.rename(columns={
            'maintenance_count': 'maintenance_count_estimate',
            'urgency_score': 'urgency_estimate'
        }, inplace=True)
        
        monthly_forecasts = forecast_df.to_dict('records')
        
        total_annual = forecast_df['forecasted_cost'].sum()
        total_remaining = forecast_df[forecast_df['date_column'] > datetime.now()]['forecasted_cost'].sum()
        
        # Get model performance metrics
        r2 = trained["performance"]["r2"]
        mae = trained["performance"]["mae"]
        mape = trained["performance"]["mape"]

        return {
            'success': True,
            'forecast_year': year,
            'monthly_forecasts': monthly_forecasts,
            'maintenance_schedule': maintenance_schedule,
            'total_annual_cost': total_annual,
            'total_remaining_cost': total_remaining,
            'average_monthly_cost': total_annual / 12,
            'data_points_used': len(monthly_data_clean),
            'model_performance': {
                'r2_score': float(r2),
                'mae': float(mae),
                'mape': float(mape),
                'silhouette_score': float(trained["silhouette_score"]),
                'clusters_used': int(trained["best_n_clusters"])
            }
        }
        
    except Exception as e:
        logger.error(f"Error in generate_maintenance_forecast: {str(e)}")
        import traceback
        logger.error(f"Traceback: {traceback.format_exc()}")
        raise

# --- Maintenance Forecast API Endpoint ---
@app.post("/api/forecast/maintenance")
def forecast_maintenance(request: MaintenanceForecastRequest):
    try:
        logger.info(f"Received maintenance forecast request for year: {request.year}")
        result = generate_maintenance_forecast(request.csv_data, request.year)
        logger.info(f"Maintenance forecast completed successfully: {len(result['monthly_forecasts'])} months, {len(result['maintenance_schedule'])} schedule items")
        return result
    except Exception as e:
        logger.error(f"Error in forecast_maintenance: {str(e)}")
        import traceback
        logger.error(f"Traceback: {traceback.format_exc()}")
        raise HTTPException(status_code=500, detail=str(e))