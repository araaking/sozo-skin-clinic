import os
from concurrent.futures import ThreadPoolExecutor
from pdf2image import convert_from_path

# ==================== PENGATURAN ====================
pdf_folder = "./promo"          # Folder berisi file .pdf
output_folder = "./jpg_outputs" # Folder hasil gambar JPG

# Path Poppler di komputer kamu
poppler_path = r"C:\poppler-26.02.0\Library\bin"

# Jumlah core CPU yang digunakan untuk rendering Poppler
THREAD_COUNT = 8 

# Jumlah worker untuk simpan file JPG secara paralel
MAX_WORKERS = 8 
# ====================================================

def save_image(args):
    """Fungsi helper untuk menyimpan gambar secara paralel"""
    image, output_filepath = args
    image.save(output_filepath, 'JPEG', quality=95)
    return output_filepath

def process_pdf(pdf_file, bulan_promo):
    pdf_path = os.path.join(pdf_folder, pdf_file)
    pdf_name = os.path.splitext(pdf_file)[0]
    print(f"🚀 Memproses: {pdf_file}...")
    
    try:
        # Rendering halaman PDF
        images = convert_from_path(
            pdf_path, 
            dpi=200, 
            poppler_path=poppler_path,
            thread_count=THREAD_COUNT
        )
        
        tasks = []
        total_pages = len(images)
        
        for i, image in enumerate(images):
            # Logika Penamaan File Output
            if bulan_promo:
                if total_pages == 1:
                    # Hasil jika cuma 1 halaman: promo_agustus.jpg
                    output_filename = f"promo_{bulan_promo}.jpg"
                else:
                    # Hasil jika banyak halaman: promo_agustus_1.jpg, promo_agustus_2.jpg
                    output_filename = f"promo_{bulan_promo}_{i + 1}.jpg"
            else:
                # Jika input bulan dikosongkan (Enter), pakai nama asli PDF
                output_filename = f"{pdf_name}_halaman_{i + 1}.jpg"

            output_filepath = os.path.join(output_folder, output_filename)
            tasks.append((image, output_filepath))
            
        # Simpan semua gambar sekaligus menggunakan ThreadPool
        with ThreadPoolExecutor(max_workers=MAX_WORKERS) as executor:
            results = list(executor.map(save_image, tasks))
            
        print(f"✅ Selesai ({len(results)} halaman tersimpan)\n")
        
    except Exception as e:
        print(f"❌ Gagal memproses {pdf_file}: {e}\n")

if __name__ == "__main__":
    if not os.path.exists(output_folder):
        os.makedirs(output_folder)

    if not os.path.exists(pdf_folder):
        os.makedirs(pdf_folder)
        print(f"Folder '{pdf_folder}' telah dibuat. Masukkan file PDF kamu di sana.")
    else:
        pdf_files = [f for f in os.listdir(pdf_folder) if f.lower().endswith('.pdf')]
        
        if not pdf_files:
            print(f"Tidak ada file PDF di folder '{pdf_folder}'.")
        else:
            # Minta input nama bulan dari user
            bulan_input = input("Masukkan nama bulan promo (misal: agustus / september): ").strip().lower()
            
            print(f"\n⚡ Ditemukan {len(pdf_files)} file PDF. Memulai konversi...\n")
            for pdf_file in pdf_files:
                process_pdf(pdf_file, bulan_input)
                
            print("🎉 Semua proses konversi selesai!")