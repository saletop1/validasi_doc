import os
from flask import Flask, request, jsonify
from pyrfc import Connection, ABAPApplicationError, ABAPRuntimeError
import logging
from flask_cors import CORS

logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(levelname)s - %(message)s',
    datefmt='%Y-%m-%d %H:%M:%S'
)

app = Flask(__name__)
CORS(app)

def connect_sap(user, passwd):
    """Membuka koneksi ke SAP."""
    try:
        ashost = os.getenv("SAP_ASHOST", "192.168.254.154")
        sysnr = os.getenv("SAP_SYSNR", "01")
        client = os.getenv("SAP_CLIENT", "300")
        conn = Connection(user=user, passwd=passwd, ashost=ashost, sysnr=sysnr, client=client, lang="EN")
        logging.info("Koneksi SAP berhasil dibuat.")
        return conn
    except Exception as e:
        logging.error(f"Gagal saat membuka koneksi ke SAP: {e}")
        return None

@app.route('/api/sap/get_do_details', methods=['POST'])
def get_do_details():
    """
    Mengambil data mentah DO dari SAP untuk disimpan di database lokal.
    """
    data = request.get_json()
    username = data.get('username')
    password = data.get('password')
    do_number = data.get('P_VBELN')

    if not all([username, password, do_number]):
        return jsonify({"success": False, "message": "Permintaan tidak lengkap."}), 400

    conn = None
    try:
        conn = connect_sap(username, password)
        if not conn:
            return jsonify({"success": False, "message": "Koneksi SAP gagal."}), 500

        logging.info(f"Memanggil RFC Z_FM_YSDR002 untuk DO: {do_number}")

        # Menggunakan nama RFC yang benar
        result = conn.call('Z_FM_YSDR002', P_VBELN=do_number)

        # Validasi error dari SAP
        return_msgs = result.get('RETURN', [])
        if any(msg.get('TYPE') in ('E', 'A') for msg in return_msgs):
             error_msg = next((msg['MESSAGE'] for msg in return_msgs if msg['TYPE'] in ('E', 'A')), "Error dari SAP.")
             logging.error(f"RFC Gagal: {error_msg}")
             return jsonify({"success": False, "message": error_msg}), 404

        # Ambil data mentah dari tabel T_DATA
        t_data = result.get('T_DATA', [])

        # PERUBAHAN: Sekarang mengambil data T_DATA2 dari hasil RFC
        t_data2 = result.get('T_DATA2', [])

        if not t_data:
            return jsonify({"success": False, "message": f"DO {do_number} tidak memiliki item."}), 404

        # Kirim data mentah ke Laravel
        raw_sap_data = {
            "success": True,
            "data": {
                "t_data": t_data,
                "t_data2": t_data2
            }
        }

        logging.info(f"Berhasil mengambil {len(t_data)} item dan {len(t_data2)} detail untuk DO {do_number}.")
        return jsonify(raw_sap_data)

    except Exception as e:
        error_msg = f"Error saat memproses DO: {str(e)}"
        logging.error(error_msg, exc_info=True)
        return jsonify({"success": False, "message": error_msg}), 500
    finally:
        if conn:
            conn.close()
            logging.info("Koneksi SAP ditutup.")

if __name__ == '__main__':
    app.run(host='127.0.0.1', port=5002, debug=True)

