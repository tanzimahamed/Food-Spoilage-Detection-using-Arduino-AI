"""
train_model.py
Trains a Random Forest classifier on historical sensor_data readings
pulled straight from the MySQL database, then saves the model for
predict.py to use.

Requirements:
    pip install pandas scikit-learn sqlalchemy pymysql joblib

Usage:
    python train_model.py
"""

import joblib
import pandas as pd
from sqlalchemy import create_engine
from sklearn.model_selection import train_test_split
from sklearn.ensemble import RandomForestClassifier
from sklearn.metrics import classification_report

# --- Config: match config/database.php ---
DB_USER = "root"
DB_PASS = ""
DB_HOST = "127.0.0.1"
DB_NAME = "food_spoilage"

MODEL_PATH = "model.pkl"


def load_data():
    engine = create_engine(f"mysql+pymysql://{DB_USER}:{DB_PASS}@{DB_HOST}/{DB_NAME}")
    df = pd.read_sql("SELECT gas_value, ph_value, temperature, humidity, status FROM sensor_data", engine)
    return df


def main():
    df = load_data()

    if len(df) < 20:
        print(f"Only {len(df)} rows found. Collect more sensor data before training "
              f"for a reliable model (aim for 200+ readings across all classes).")

    X = df[["gas_value", "ph_value", "temperature", "humidity"]]
    y = df["status"]

    X_train, X_test, y_train, y_test = train_test_split(
        X, y, test_size=0.2, random_state=42, stratify=y if y.nunique() > 1 else None
    )

    model = RandomForestClassifier(n_estimators=200, random_state=42)
    model.fit(X_train, y_train)

    preds = model.predict(X_test)
    print("Model evaluation on held-out test set:")
    print(classification_report(y_test, preds, zero_division=0))

    joblib.dump(model, MODEL_PATH)
    print(f"Model saved to {MODEL_PATH}")


if __name__ == "__main__":
    main()
