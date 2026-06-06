from pathlib import Path

from PIL import Image


ROOT = Path(__file__).resolve().parents[1]
SOURCE_DIR = ROOT / "assets" / "theme-icons"
OUTPUT_DIR = SOURCE_DIR / "web"
MAX_SIZE = (256, 256)
WEBP_QUALITY = 82

THEME_ICONS = {
    "directehulp": "directehulp-transparant.png",
    "familie-omgeving": "familie-omgeving-transparant.png",
    "gezondheid-welzijn": "gezondheid-welzijn-transparant.png",
    "hulp-ondersteuning": "hulp-ondersteuning-transparant.png",
    "school-toekomst": "school-toekomst-transparant.png",
    "veiligheid-rechten": "veiligheid-rechten-transparant.png",
    "vrijetijd-ontwikkeling": "vrijetijd-ontwikkeling-transparant.png",
    "wonen-verblijf": "wonen-verblijf-transparant.png",
}


def optimized_image(source: Path) -> Image.Image:
    image = Image.open(source).convert("RGBA")
    image.thumbnail(MAX_SIZE, Image.Resampling.LANCZOS)
    return image


def has_transparency(image: Image.Image) -> bool:
    if image.mode != "RGBA":
      return False
    alpha = image.getchannel("A")
    return alpha.getextrema()[0] < 255


def optimize_icon(name: str, source_name: str) -> tuple[str, int, int, bool] | None:
    source = SOURCE_DIR / source_name
    if not source.exists():
        print(f"missing source: {source.relative_to(ROOT)}")
        return None

    OUTPUT_DIR.mkdir(parents=True, exist_ok=True)
    image = optimized_image(source)
    transparent = has_transparency(image)

    webp = OUTPUT_DIR / f"{name}.webp"
    png = OUTPUT_DIR / f"{name}-256.png"
    image.save(webp, "WEBP", quality=WEBP_QUALITY, method=6)
    image.save(png, "PNG", optimize=True)

    return name, source.stat().st_size, webp.stat().st_size, transparent


def main() -> None:
    results = []
    missing = []
    for name, source_name in THEME_ICONS.items():
        result = optimize_icon(name, source_name)
        if result is None:
            missing.append(source_name)
        else:
            results.append(result)

    for name, source_size, webp_size, transparent in results:
        state = "alpha ok" if transparent else "no alpha detected"
        print(f"{name}: {source_size} -> {webp_size} bytes ({state})")

    if missing:
        print("missing:", ", ".join(missing))

    print(f"optimized {len(results)} theme icons")


if __name__ == "__main__":
    main()
