# Multi-Agent Workflows with NuGen

![NuGen Logo](https://nugen.in/logo.png)

**Designing Collaborative AI Systems using NuGen Agents**  

Domain-aligned foundational models at industry-leading speeds with zero data retention.  
Learn more at: [NuGen Docs](https://docs.nugen.in/introduction)

## Overview

Most AI applications rely on a single agent to handle every task—planning, reasoning, research, validation, and response generation. While simple, this approach struggles with complex, multi-step tasks or high-stakes workflows.  

This cookbook demonstrates how to build **Agent-Oriented Systems** using NuGen Agents — multiple specialized agents collaborating like a real-world team.

You will learn to:
- Design specialized AI agents with clear responsibilities
- Orchestrate agents into a workflow
- Improve reliability, reasoning, and output quality
- Debug and troubleshoot agent behavior

## Problem Statement

Single-agent systems often face:
- Cognitive overload
- Hallucinations
- Poor multi-step reasoning
- Lack of verification

**Solution:** Split responsibilities across multiple agents so that **each agent does one thing well**.

## Solution Architecture

We implement a **4-Agent system**:

| Agent | Role | Temperature |
|-------|------|-------------|
| Planner Agent | Breaks tasks into steps | Low (deterministic) |
| Research Agent | Gathers structured information | Medium |
| Validator Agent | Checks factual correctness | Very Low |
| Writer Agent | Produces final user-facing response | Medium-High |

Each agent is independently configurable, powered by NuGen models, and optimized with instructions and demonstrations.

## Use Case Example

**User Input:**
> "Analyze India's EV policy and summarize opportunities for startups."

**Expected Outcome:**  
A structured, validated, high-quality report created through agent collaboration.

## Prerequisites

- NuGen API Key  
- Python 3.9+  
- Basic understanding of REST APIs

## Environment Setup

Install dependencies:

```bash
pip install --quiet requests python-dotenv
````

Load environment variables:

```python
import os
import requests
from dotenv import load_dotenv

load_dotenv()
API_KEY = os.getenv("NUGEN_API_KEY")
BASE_URL = "https://api.nugen.in/agents"

headers = {
    "Authorization": f"Bearer {API_KEY}",
    "Content-Type": "application/json"
}
```

## Agent Design Principles

* One clear responsibility per agent
* Task-specific instructions
* Temperature tuned per agent role
* Include demonstrations when needed

## Agent Setup

**Planner Agent:** Breaks user requests into step-by-step plans

**Research Agent:** Collects structured, factual information

**Validator Agent:** Detects hallucinations, missing logic, and errors

**Writer Agent:** Produces the final polished output

## Workflow Orchestration

1. User Input
2. Planner Agent
3. Research Agent
4. Validator Agent
5. Writer Agent

Helper function to run agents:

```python
def run_agent(agent_id, message):
    payload = {"message": message}
    response = requests.post(
        f"{BASE_URL}/{agent_id}/run/",
        json=payload,
        headers=headers
    )
    return response.json()["response"]
```

## Full workflow execution:

```python
user_query = "Analyze India's EV policy and summarize opportunities for startups."

plan = run_agent(planner_id, user_query)
research = run_agent(research_id, plan)
validation = run_agent(validator_id, research)

final_input = f"""
Research Output:
{research}

Validation Feedback:
{validation}
"""

final_response = run_agent(writer_id, final_input)
print(final_response)
```

## Example Output

The final response is:

* Structured
* Fact-checked
* Clear
* Ready for real-world use

## Troubleshooting

* **Planner too vague** → Reduce temperature, add step-format constraints
* **Research hallucinations** → Lower temperature, rely on Validator Agent
* **Verbose Writer responses** → Tighten instructions

## Best Practices

* One responsibility per agent
* Deterministic agents for reasoning tasks
* Creative agents only at final stage
* Always validate before publishing

## Conclusion

This cookbook demonstrates how NuGen Agents can collaborate in multi-agent workflows that outperform single-agent systems.

Reusable blueprint for:

* Research assistants
* Policy analysis tools
* Enterprise AI workflows
* Reliable agentic systems

