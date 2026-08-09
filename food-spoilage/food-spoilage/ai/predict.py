"""
predict.py
Predicts Fresh / Warning / Spoiled for a given sensor reading.

Uses the trained Random Forest model (model.pkl) if it exists,
otherwise falls back to the same simple rule used in api/insert.php
so the system still works before you've collected training data.

Usage:
    python predict.py --gas 320 --ph 6.1 --temperature 27.5 --humidity 68
"""

import argparse
import os
import sys

MODEL_PATH = os.path.join(os.path.dirname(__file__), "model.pkl")


def rule_based_predict(gas, ph, temperature, humidity):
    if gas > 400 and ph < 5.5:
        return "Spoiled"
    if gas > 250:
        return "Warning"
    return "Fresh"


def model_predict(gas, ph, temperature, humidity):
    import joblib
    import pandas as pd

    model = joblib.load(MODEL_PATH)
    X = pd.DataFrame([{
        "gas_value": gas,
        "ph_value": ph,
        "temperature": temperature,
        "humidity": humidity,
    }])
    return model.predict(X)[0]


def predict(gas, ph, temperature, humidity):
    if os.path.exists(MODEL_PATH):
        try:
            return model_predict(gas, ph, temperature, humidity), "model"
        except Exception as e:
            print(f"Warning: model prediction failed ({e}); using rule-based fallback.", file=sys.stderr)
    return rule_based_predict(gas, ph, temperature, humidity), "rules"


def main():
    parser = argparse.ArgumentParser(description="Predict food spoilage status from sensor readings.")
    parser.add_argument("--gas", type=float, required=True, help="Gas sensor value (ppm)")
    parser.add_argument("--ph", type=float, required=True, help="pH value")
    parser.add_argument("--temperature", type=float, required=True, help="Temperature (°C)")
    parser.add_argument("--humidity", type=float, required=True, help="Humidity (%)")
    args = parser.parse_args()

    status, source = predict(args.gas, args.ph, args.temperature, args.humidity)
    print(f"Predicted status: {status}  (source: {source})")


if __name__ == "__main__":
    main()
