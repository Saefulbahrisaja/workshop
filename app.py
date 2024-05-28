import pandas as pd
import numpy as np
from sklearn.model_selection import train_test_split
from sklearn.ensemble import RandomForestClassifier
from sklearn.metrics import accuracy_score
import pickle
import pymysql

# Membaca data dari file CSV
data = pd.read_csv('data_set.csv')

# Melakukan preprocessing data
data['bulan'] = pd.to_datetime(data['tgl_transaksi']).dt.month
data['tahun'] = pd.to_datetime(data['tgl_transaksi']).dt.year

# Membuat feature dan target
X = data[['bulan', 'tahun', 'nama_produk', 'harga']]
y = data['jumlah']

# Encoding categorical data
X = pd.get_dummies(X, columns=['bulan', 'tahun', 'nama_produk'])

# Split data menjadi training dan testing set
X_train, X_test, y_train, y_test = train_test_split(X, y, test_size=0.2, random_state=42)

# Membuat model Random Forest
model = RandomForestClassifier(n_estimators=100, random_state=42)
model.fit(X_train, y_train)

# Evaluasi model
y_pred = model.predict(X_test)
print(f'Akurasi Model: {accuracy_score(y_test, y_pred)}')

# Menyimpan model
with open('model_prediksi.pkl', 'wb') as file:
    pickle.dump(model, file)

# Membuat API untuk deployment
from flask import Flask, request, jsonify

app = Flask(__name__)

@app.route('/predict', methods=['POST'])
def predict():
    data = request.get_json()
    bulan = data['bulan']
    tahun = data['tahun']
    nama_produk = data['nama_produk']
    harga = data['harga']
    input_data = pd.DataFrame([[bulan, tahun, nama_produk, harga]], columns=['bulan', 'tahun', 'nama_produk', 'harga'])
    input_encoded = pd.get_dummies(input_data)
    # Memastikan semua feature yang ada di training ada di input data
    input_encoded = input_encoded.reindex(columns = X.columns, fill_value=0)
    loaded_model = load_model()
    prediction = loaded_model.predict(input_encoded)
    return jsonify({'prediksi_jumlah': int(prediction[0])})

def load_model():
    with open('model_prediksi.pkl', 'rb') as file:
        loaded_model = pickle.load(file)
    return loaded_model

@app.route('/get_produk', methods=['GET'])
def get_produk():
    # Menghubungkan ke database MySQL
    connection = pymysql.connect(host='localhost',
                                 user='root',
                                 password='root',
                                 db='db_pemesanan',
                                 charset='utf8mb4',
                                 cursorclass=pymysql.cursors.DictCursor)
    try:
        with connection.cursor() as cursor:
            # Query untuk mengambil semua nama produk unik
            sql = "SELECT DISTINCT nama_produk FROM tbl_produk"
            cursor.execute(sql)
            result = cursor.fetchall()
            produk_list = [row['nama_produk'] for row in result]
            return jsonify({'produk': produk_list})
    finally:
        connection.close()

if __name__ == '__main__':
    app.run(debug=True)
