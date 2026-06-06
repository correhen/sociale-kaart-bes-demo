from pathlib import Path

from PIL import Image


ROOT = Path(__file__).resolve().parents[1]
QUALITY = 86


THEME_ICONS = [
    "directehulp",
    "familie-omgeving",
    "gezondheid-welzijn",
    "hulp-ondersteuning",
    "school-toekomst",
    "veiligheid-rechten",
    "vrijetijd-ontwikkeling",
    "wonen-verblijf",
]

BRAND_ASSETS = {
    "kadena-hubenil-beeld": [512, 256],
    "kadena-hubenil-tekst": [900, 450],
    "logo-kadena-hubenil": [900, 450],
}


def save_webp(source: Path, output: Path, size: tuple[int, int] | None = None) -> None:
    output.parent.mkdir(parents=True, exist_ok=True)
    with Image.open(source) as image:
        image = image.convert("RGBA")
        if size:
            image.thumbnail(size, Image.Resampling.LANCZOS)
        image.save(output, "WEBP", quality=QUALITY, method=6)


def optimize_theme_icons() -> None:
    source_dir = ROOT / "assets" / "theme-icons"
    output_dir = source_dir / "web"
    for name in THEME_ICONS:
        source = source_dir / f"{name}.png"
        if not source.exists():
            print(f"skip missing {source}")
            continue
        save_webp(source, output_dir / f"{name}-256.webp", (256, 256))
        save_webp(source, output_dir / f"{name}-128.webp", (128, 128))


def optimize_brand_assets() -> None:
    source_dir = ROOT / "assets" / "brand"
    output_dir = source_dir / "web"
    for name, widths in BRAND_ASSETS.items():
        source = source_dir / f"{name}.png"
        if not source.exists():
            print(f"skip missing {source}")
            continue
        with Image.open(source) as image:
            width, height = image.size
        for target_width in widths:
            target_height = max(1, round(height * (target_width / width)))
            save_webp(source, output_dir / f"{name}-{target_width}.webp", (target_width, target_height))


def main() -> None:
    optimize_theme_icons()
    optimize_brand_assets()
    print("optimized Kadena Hubenil assets")


if __name__ == "__main__":
    main()
