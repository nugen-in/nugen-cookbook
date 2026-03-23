# Structured Information Extraction with Nugen

## Problem Statement

Unstructured text — emails, incident reports, contracts, medical notes — contains critical information that is hard to query, aggregate, or act on programmatically. Manually extracting fields like names, dates, amounts, or classifications from thousands of documents is slow and error-prone.

This cookbook shows how to use **Nugen's chat completion API** to extract structured JSON data from arbitrary unstructured text in a single prompt — no fine-tuning required.

## Target Audience

Developers and data engineers who need to:
- Convert free-form documents into database-ready records
- Build ETL pipelines over unstructured text
- Automate data entry from emails, reports, or forms

No prior machine learning experience is needed — only basic Python.

## Prerequisites

| Requirement | Details |
|---|---|
| Nugen API key | Free at [Nugen Dashboard](https://nugen-platform-frontend.azurewebsites.net/dashboard) |
| Python | 3.9 or higher |
| Packages | `requests`, `python-dotenv` (see Step 1 in the notebook) |

Set your API key in a `.env` file in the same directory as the notebook:

```bash
NUGEN_API_KEY=your_api_key_here
```

## What You Will Build

A reusable `extract()` function that accepts:
1. **Any unstructured text** (email, report, form)
2. **A target schema** (described in plain English)

And returns a **validated Python dict** with the extracted fields — ready to insert into a database, send to an API, or feed into downstream processing.

## Expected Outcomes

By the end of the notebook you will have:
- Extracted structured data from sample emails, incident reports, and purchase orders
- Understood how to design extraction schemas for your own domain
- A production-ready extraction helper you can drop into any Python project

## Notebook Structure

| Section | What it covers |
|---|---|
| Steps 1–4 | Setup, imports, configuration |
| Step 5 | Core `extract()` function with retry logic |
| Steps 6–8 | Three worked examples (email, incident report, purchase order) |
| Step 9 | Batch processing multiple documents |
| Step 10 | Troubleshooting & extension ideas |

## Troubleshooting

See the **Troubleshooting** section at the end of the notebook for common errors and fixes.

## Relevant Nugen Documentation

- [Nugen API Reference](https://docs.nugen.in/api-reference)
- [Chat Completions endpoint](https://docs.nugen.in/api-reference/chat-completions)
- [Nugen model overview](https://docs.nugen.in/introduction)
