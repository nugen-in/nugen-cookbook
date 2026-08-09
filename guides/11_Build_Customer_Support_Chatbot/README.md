# Customer Support Chatbot with Nugen

Build a simple support chatbot using Nugen's Agents API.

## What we're building

A chatbot for "TechStore Electronics" that handles:
- Product questions
- Order tracking  
- Returns & refunds
- Basic troubleshooting

## Before you start

1. Get your API key from [platform.nugen.in](https://platform.nugen.in)
2. Have Python 3.8+ installed
3. That's it!

## Run the notebook

```bash
pip install requests
jupyter notebook customer_support_chatbot.ipynb
```

## Common issues

| Problem | Fix |
|---------|-----|
| 401 error | Check your API key |
| No response | Use `llama-v3p3-70b-instruct` model |
| Rate limited | Add `time.sleep(1)` between calls |

## Links

- [Nugen Docs](https://docs.nugen.in)
- [API Reference](https://docs.nugen.in/api-reference)
- [Get API Key](https://platform.nugen.in)
