"""A tiny Flask service — transform-my-repo eval fixture (GO scenario).

Answer key: this is a same-ecosystem (Python → Python) framework migration to
FastAPI. Every dependency has a direct, well-maintained target equivalent
(flask → fastapi, both on PyPI; requests keeps working, or → httpx for async).
The one real semantic gap is the sync-handler → async-handler model. A
transform-my-repo run should reach a GO verdict with that gap named, NOT
inflate the difficulty, and NOT claim any missing equivalent.
"""

from flask import Flask, jsonify, request
import requests

app = Flask(__name__)


@app.get("/health")
def health():
    return jsonify({"status": "ok"})


@app.get("/rate")
def rate():
    # Synchronous outbound call — the load-bearing detail for the port:
    # in FastAPI this handler would become `async def` using httpx.
    resp = requests.get("https://example.com/rates", timeout=5)
    return jsonify({"rate": resp.json().get("eur")})


if __name__ == "__main__":
    app.run(port=int(request.environ.get("PORT", 5000)))
