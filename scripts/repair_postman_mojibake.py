import json
from pathlib import Path


FILE = Path("Restaurant API.postman_collection.json")

MOJIBAKE_MARKERS = (
    "Ã", "Â", "Ä", "Æ", "Ð", "ð", "á»", "áº", "â€", "â€“", "â€”", "â€˜", "â€™", "â€œ", "â€�"
)


def looks_broken(value: str) -> bool:
    return any(marker in value for marker in MOJIBAKE_MARKERS)


def try_fix_once(value: str) -> str:
    for source_encoding in ("latin1", "cp1252"):
        try:
            repaired = value.encode(source_encoding).decode("utf-8")
            if repaired != value:
                return repaired
        except (UnicodeEncodeError, UnicodeDecodeError):
            continue
    return value


def repair_string(value: str) -> str:
    current = value
    for _ in range(5):
        if not looks_broken(current):
            break
        repaired = try_fix_once(current)
        if repaired == current:
            break
        current = repaired
    return current


def repair_node(node):
    if isinstance(node, dict):
        return {repair_string(key) if isinstance(key, str) else key: repair_node(val) for key, val in node.items()}
    if isinstance(node, list):
        return [repair_node(item) for item in node]
    if isinstance(node, str):
        return repair_string(node)
    return node


data = json.loads(FILE.read_text(encoding="utf-8"))
repaired = repair_node(data)
FILE.write_text(json.dumps(repaired, ensure_ascii=False, indent=4), encoding="utf-8")
print("Repaired mojibake in Postman collection.")
