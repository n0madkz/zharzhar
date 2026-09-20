"""2GIS collector compatible with parser-2gis JSON output.

Recent 2GIS pages navigate away when parser-2gis clicks a result, which makes
the remaining DOM nodes stale. The public search page already contains the
same catalog items in its server-rendered state, so we read that state first
and retain the upstream parser as a fallback for future markup changes.
"""
from __future__ import annotations

import ast
import json
import os
import re
import sys
import time
from pathlib import Path
from urllib.parse import urlsplit

import requests


os.environ.pop("DEBUG", None)


def _argument(name: str, default: str | None = None) -> str | None:
    try:
        return sys.argv[sys.argv.index(name) + 1]
    except (ValueError, IndexError):
        return default


def _page_url(url: str, page: int) -> str:
    base = re.sub(r"/page/\d+/?$", "", url.rstrip("/"), flags=re.I)
    return base if page == 1 else f"{base}/page/{page}"


def _profiles(html: str) -> list[dict]:
    match = re.search(r"var initialState = JSON\.parse\('(.*?)'\);", html, re.S)
    if not match:
        return []

    state = json.loads(ast.literal_eval("'" + match.group(1) + "'"))
    profiles = state.get("data", {}).get("entity", {}).get("profile", {})
    return [entry.get("data", entry) for entry in profiles.values() if isinstance(entry, dict)]


def collect(url: str, limit: int) -> list[dict]:
    host = (urlsplit(url).hostname or "").lower()
    if host != "2gis.kz" and not host.endswith(".2gis.kz"):
        raise ValueError("Only public 2gis.kz search URLs are supported.")

    session = requests.Session()
    session.headers.update({
        "Accept-Language": "ru-KZ,ru;q=0.9,kk;q=0.8",
        "User-Agent": "Mozilla/5.0",
    })
    records: list[dict] = []
    seen: set[str] = set()
    max_pages = min(100, max(1, (limit + 11) // 12 + 1))

    for page in range(1, max_pages + 1):
        response = session.get(_page_url(url, page), timeout=45)
        response.raise_for_status()
        fresh = []
        for item in _profiles(response.text):
            source_id = str(item.get("id", "")).split("_", 1)[0]
            if not source_id or source_id in seen:
                continue
            seen.add(source_id)
            fresh.append(item)
            if len(records) + len(fresh) >= limit:
                break
        records.extend(fresh)
        if not fresh or len(records) >= limit:
            break
        time.sleep(0.2)

    return records[:limit]


def main() -> None:
    url = _argument("-i")
    output = _argument("-o")
    limit = int(_argument("--parser.max-records", "1000") or "1000")
    if not url or not output:
        raise SystemExit("Both -i URL and -o output path are required.")

    try:
        records = collect(url, limit)
    except Exception as error:
        print(f"Server-rendered 2GIS collection failed: {error}", file=sys.stderr)
        records = []

    if not records:
        from parser_2gis import main as upstream_main

        upstream_main()
        return

    target = Path(output)
    target.parent.mkdir(parents=True, exist_ok=True)
    target.write_text(json.dumps(records, ensure_ascii=False, indent=2), encoding="utf-8")
    print(f"Collected {len(records)} public 2GIS catalog records.")


if __name__ == "__main__":
    main()
