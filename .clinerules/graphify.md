## Graphify — codebase knowledge graph (graphify-out/graph.json)

A precomputed code graph of this repo exists at `graphify-out/graph.json` (2077 nodes, 4015 edges, 261 communities).

Before running broad `search_codebase` sweeps for codebase/architecture questions, query the graph first with `graphify query "<question>"` (or read the relevant community from `graphify-out/graph.json`) and only then open a small, targeted set of files. This is significantly cheaper in tokens than wide grep/read sweeps.

- Query examples: `graphify query "where is user authentication handled?"`, `graphify path "A" "B"`, `graphify explain "ClassName"`, `graphify god-nodes`
- After significant code changes, refresh with: `graphify update .` (no LLM key needed)
- `graphify-out/` is a generated artifact: never edit it manually and never commit it to git.
