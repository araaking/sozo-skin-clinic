"""
Remove BG gratis - rekomendasi: rembg + isnet-general-use
Kualitas hampir BRIA 2.0 tapi install ringan dan cepet di CPU.

Install:
  pip install rembg onnxruntime pillow

Pakai:
  python ads/remove_bg.py input.jpg              -> jadi input.png (transparan)
  python ads/remove_bg.py input.jpg output.png   -> custom output
  python ads/remove_bg.py folder_input           -> batch semua jpg/png di folder
"""
import sys
from pathlib import Path
from rembg import remove, new_session

# Model terbaik yang gratis & ringan: isnet-general-use
# Alternatif berat tapi paling rapi: birefnet-general (butuh download ~1GB)
MODEL = "isnet-general-use"
session = new_session(MODEL)

def process_file(inp: Path, out: Path):
    data = inp.read_bytes()
    result = remove(data, session=session)
    out.parent.mkdir(parents=True, exist_ok=True)
    out.write_bytes(result)
    print(f"✓ {inp.name} -> {out.name}")

def main():
    if len(sys.argv) < 2:
        print("Pakai: python ads/remove_bg.py <file/folder> [output.png]")
        sys.exit(1)

    inp = Path(sys.argv[1])

    # Mode 1: input folder -> batch
    if inp.is_dir():
        out_dir = Path(sys.argv[2]) if len(sys.argv) > 2 else inp.parent / f"{inp.name}_no_bg"
        for p in inp.glob("*"):
            if p.suffix.lower() in [".jpg", ".jpeg", ".png", ".webp"]:
                process_file(p, out_dir / f"{p.stem}.png")
        print(f"\nSelesai. Hasil di: {out_dir}")
        return

    # Mode 2: single file
    if not inp.exists():
        print(f"File tidak ada: {inp}")
        sys.exit(1)

    if len(sys.argv) > 2:
        out = Path(sys.argv[2])
    else:
        out = inp.with_suffix(".png")
        # hindari overwrite file input kalau sudah png
        if out == inp:
            out = inp.with_name(f"{inp.stem}_no_bg.png")

    process_file(inp, out)

if __name__ == "__main__":
    main()
