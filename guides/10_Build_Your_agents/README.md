# Build Your Agents with Nugen

<img src="https://nugen.in/logo.png" alt="Nugen Logo" width="200"/>

## Overview

This cookbook demonstrates how to create, manage, and utilize AI agents using the Nugen API platform. Nugen Agents are customizable AI assistants that can be tailored to your specific needs, providing consistent, high-quality responses for your applications.

## What You'll Learn

- ✅ Create custom AI agents with specific instructions
- ✅ Configure agents with demonstrations (few-shot learning)
- ✅ Run inference through agents
- ✅ Update and manage agent configurations
- ✅ Monitor usage statistics
- ✅ Delete agents when no longer needed

## Prerequisites

- **Python 3.8+**
- **Nugen API Key** - Get your free API key from [platform.nugen.in](https://platform.nugen.in)
- Basic understanding of REST APIs

## Installation

```bash
pip install requests python-dotenv
```

## Quick Start

1. Clone this repository
2. Get your API key from [Nugen Platform](https://platform.nugen.in)
3. Open `agents.ipynb` in Jupyter or VS Code
4. Replace `<your-api-key-here>` with your actual API key
5. Run the cells to create your first agent!

## Available Models

Nugen offers various models for agents including:

| Model | Description |
|-------|-------------|
| `nugen-flash-instruct` | Fast, general-purpose model |
| `llama-v3p3-70b-instruct` | Large, powerful instruction-following model |
| `deepseek-v3p2` | Advanced reasoning model |
| `qwen3-8b` | Efficient multilingual model |

## Agent Configuration Options

| Parameter | Description | Default |
|-----------|-------------|---------|
| `agent_name` | Unique name for your agent | Required |
| `model` | The LLM model to use | `nugen-flash-instruct` |
| `temperature` | Creativity (0.0-1.0) | 0.7 |
| `instructions` | System prompt for the agent | Required |
| `demonstrations` | Few-shot examples | Optional |

## Example Use Cases

1. **Customer Support Agent** - Handle common queries with consistent responses
2. **Technical Assistant** - Provide code explanations and debugging help
3. **Content Writer** - Generate marketing copy with specific tone
4. **Research Assistant** - Summarize and analyze documents

## Troubleshooting

| Issue | Solution |
|-------|----------|
| 401 Unauthorized | Check your API key is valid |
| 500 Internal Error | Try a different model (e.g., `llama-v3p3-70b-instruct`) |
| Model not responding | Some models may have limited availability |
| Rate limiting | Add delays between requests |

## API Endpoints

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/agents/available_models/` | GET | List available models |
| `/agents/create/` | POST | Create new agent |
| `/agents/` | GET | List your agents |
| `/agents/{id}` | GET | Get agent details |
| `/agents/{id}/run/` | POST | Run inference |
| `/agents/{id}` | PATCH | Update agent |
| `/agents/{id}` | DELETE | Delete agent |
| `/agents/usage/` | GET | Get usage statistics |

## Resources

- [Nugen Documentation](https://docs.nugen.in)
- [API Reference](https://docs.nugen.in/api-reference)
- [Nugen Platform](https://platform.nugen.in)

## Contributing

We welcome contributions! Please see our [Contributing Guide](../../CONTRIBUTING.md) for details.

## License

This project is licensed under the MIT License - see the [LICENSE](../../LICENSE) file for details.
