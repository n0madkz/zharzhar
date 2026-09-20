"""Compatibility launcher for interlark/parser-2gis on the current 2GIS site."""

import json
import os
import queue
import re

from parser_2gis import main
from parser_2gis.chrome import ChromeRemote

os.environ.pop("DEBUG", None)
_state = {}
_original_wait_response = ChromeRemote.wait_response
_original_get_response_body = ChromeRemote.get_response_body


def _compatible_wait_response(remote, response_pattern):
    for _ in range(20):
        response = _original_wait_response(remote, response_pattern)
        if response is None:
            return response
        body = _original_get_response_body(remote, response, timeout=10)
        try:
            document = json.loads(body)
        except (TypeError, json.JSONDecodeError):
            continue
        if document.get("meta", {}).get("code") == 200 and document.get("result", {}).get("items"):
            response["_broker_body"] = body
            return response
    return None


def _compatible_get_response_body(remote, response, timeout=None):
    return response.pop("_broker_body", None) or _original_get_response_body(remote, response, timeout=timeout)


ChromeRemote.wait_response = _compatible_wait_response
ChromeRemote.get_response_body = _compatible_get_response_body


def _identity(href):
    match = re.search(r"/(firm|station)/(\d+)", href or "")
    return match.groups() if match else None


def _compatible_click(remote, node, timeout=None):
    state = _state.setdefault(id(remote), {})
    current_url = remote.execute_script("window.location.href")
    if "/search/" in current_url:
        state["search_url"] = current_url
    elif state.get("search_url"):
        remote.navigate(state["search_url"], referer="https://2gis.kz", timeout=120)
        remote.wait(1)

    identity = _identity(node.attributes.get("href", ""))
    if not identity:
        raise RuntimeError("2GIS result link does not contain a firm identifier")
    state["expected_id"] = identity[1]
    fragment = json.dumps(f"/{identity[0]}/{identity[1]}")
    script = f"""(() => {{
        const link = [...document.querySelectorAll('a[href]')].find(item => item.href.includes({fragment}));
        if (!link) return false;
        link.scrollIntoView({{block: 'center'}});
        link.click();
        return true;
    }})()"""
    # 2GIS emits unrelated failed `items/byid` requests while arranging the
    # map viewport. The upstream parser otherwise consumes that stale error as
    # the clicked venue response.
    for response_queue in remote._response_queues.values():
        while True:
            try:
                response_queue.get_nowait()
            except queue.Empty:
                break
    def click_when_ready():
        for _ in range(30):
            if remote.execute_script(script):
                return True
            remote.wait(0.5)
        return False

    clicked = click_when_ready()
    if not clicked:
        if not state.get("search_url"):
            raise RuntimeError("2GIS result disappeared before it could be opened")
        remote.navigate(state["search_url"], referer="https://2gis.kz", timeout=120)
        remote.wait(1)
        if not click_when_ready():
            raise RuntimeError("2GIS result could not be reacquired")


ChromeRemote.perform_click = _compatible_click

if __name__ == "__main__":
    main()
