"""Run strict official OpenSpec adoption preflight from a prepared target checkout."""
import argparse
import json
from pathlib import Path
import sys

sys.path.insert(0, str(Path(__file__).resolve().parents[1]))
from core.adoption_preflight import PreflightError, run_adoption_preflight


def main(argv=None):
    parser = argparse.ArgumentParser()
    parser.add_argument("--root", type=Path, default=Path("."))
    parser.add_argument("--cli", default="openspec")
    args = parser.parse_args(argv)
    try:
        proof = run_adoption_preflight(args.root, cli=args.cli)
    except PreflightError as exc:
        print(json.dumps({"status": "BLOCKED", "error": str(exc)}), file=sys.stderr)
        return 1
    print(json.dumps(proof, sort_keys=True))
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
