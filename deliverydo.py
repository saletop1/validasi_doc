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
        result = conn.call('Z_FM_YSDR002', P_VBELN=do_number)

        # --- PERBAIKAN: Log mentah dari SAP dihilangkan dari terminal ---
        # logging.info(f"--- HASIL MENTAH DARI SAP UNTUK DO {do_number} ---")
        # logging.info(result)
        # logging.info("---------------------------------------------")

        t_data = result.get('T_DATA', [])
        t_data2 = result.get('T_DATA2', [])

        if t_data:
            logging.info(f"T_DATA ditemukan ({len(t_data)} baris). Melanjutkan proses.")
            raw_sap_data = {
                "success": True,
                "data": {
                    "t_data": t_data,
                    "t_data2": t_data2
                }
            }
            return jsonify(raw_sap_data)

        return_msgs = result.get('RETURN', [])
        error_msg = next((msg.get('MESSAGE') for msg in return_msgs if msg.get('TYPE') in ('E', 'A') and msg.get('MESSAGE', '').strip()), None)

        if error_msg:
            logging.error(f"RFC Gagal karena pesan RETURN dari SAP: {error_msg}")
            return jsonify({"success": False, "message": error_msg}), 404
        else:
            logging.warning(f"DO {do_number} tidak memiliki item di T_DATA. Mengembalikan 'tidak ditemukan'.")
            return jsonify({"success": False, "message": f"DO {do_number} tidak ditemukan atau tidak memiliki item."}), 404

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

