# Hallucination Detection with Summarization Agent

Detect hallucinations in AI-generated summaries using Nugen's domain-aligned models.

## Overview

This notebook demonstrates a two-step pipeline:
1. **Summarize** - Generate a concise summary of domain-specific text
2. **Verify** - Detect hallucinations by comparing summary against source

## Quick Start

```bash
# Install dependencies
pip install requests python-dotenv

# Create .env file
echo "NUGEN_API_KEY=your-api-key-here" > .env

# Run the notebook
jupyter notebook hallucination_detection.ipynb
```

## Prerequisites

- Python 3.8+
- Nugen API key ([Get one here](https://nugen-platform-frontend.azurewebsites.net/dashboard))

## What You'll Learn

- Making API calls to Nugen's v3 completion endpoint
- Crafting effective prompts for summarization
- Building fact-checking prompts for hallucination detection
- Parsing and displaying structured verification results

## Example Output

```
[HALLUCINATION CHECK] [OK] PASS: Summary is Accurate
   Confidence: HIGH
   Issues: None
   Verdict: Summary accurately reflects source document facts.
```

## Files

| File | Description |
|------|-------------|
| `hallucination_detection.ipynb` | Main notebook with complete pipeline |

## Related Cookbooks

- [Getting Started with Nugen](../01_Getting_Started_With_Nugen/)
- [Build Your Agents](../10_Build_Your_agents/)
- [Using Reasoning for Routine Generation](../05_Using_reasoning_for_routine_generation_with_Nugen/)
