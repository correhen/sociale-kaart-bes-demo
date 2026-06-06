from pathlib import Path

from PIL import Image


ROOT = Path(__file__).resolve().parents[1]
QUALITY = 86

LANDING_IMAGES = {
    "jongeren": {
        "source": ROOT / "assets" / "img" / "jongeren.png",
        "outputs": [("landing-jongeren-900.webp", 900), ("landing-jongeren-600.webp", 600)],
    },
    "jongeren-hero": {
        "source": ROOT / "assets" / "img" / "jongeren-hero.png",
        "outputs": [
            ("jongeren-hero-1400.webp", 1400),
            ("jongeren-hero-900.webp", 900),
            ("jongeren-hero-600.webp", 600),
        ],
    },
    "professionals": {
        "source": ROOT / "assets" / "img" / "professionals.png",
        "outputs": [("landing-professionals-700.webp", 700), ("landing-professionals-450.webp", 450)],
    },
}


def save_webp(source: Path, output: Path, width: int) -> None:
    output.parent.mkdir(parents=True, exist_ok=True)
    with Image.open(source) as image:
        image = image.convert("RGBA")
        ratio = width / image.width
        height = max(1, round(image.height * ratio))
        image = image.resize((width, height), Image.Resampling.LANCZOS)
        image.save(output, "WEBP", quality=QUALITY, method=6)


def main() -> None:
    output_dir = ROOT / "assets" / "img" / "web"
    for item in LANDING_IMAGES.values():
        source = item["source"]
        if not source.exists():
            print(f"skip missing {source}")
            continue
        for filename, width in item["outputs"]:
            save_webp(source, output_dir / filename, width)
    print("optimized landing images")


if __name__ == "__main__":
    main()
