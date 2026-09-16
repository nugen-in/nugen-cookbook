# Text-to-SQL with Nugen

## 📋 Problem Statement
Converting natural language questions into SQL queries is a powerful way to democratize data access. However, getting accurate SQL from LLMs can be challenging due to hallucinations or invalid syntax. This guide shows how to build a robust Text-to-SQL pipeline using Nugen.

## 🎯 Target Audience
- Data Engineers building internal data tools.
- Business Intelligence developers.
- Developers looking to add "Ask your Database" features to their apps.

## ✅ Prerequisites
- A Nugen API Key (Sign up at [nugen.in](https://nugen.in)).
- Python 3.8 or higher.
- Basic knowledge of SQL.

## 🚀 Expected Outcomes
By the end of this cookbook, you will:
1.  Learn how to effectively prompt Nugen with your database schema.
2.  Implement "Chain of Thought" reasoning to handle complex queries (JOINs, aggregations).
3.  Build a complete pipeline that generates, sanitizes, and executes SQL against a SQLite database.

## 🛠️ Setup
1.  Copy `.env.example` to `.env` and add your `NUGEN_API_KEY`.
2.  Install dependencies: `pip install -r requirements.txt`.
3.  Run the notebook `text_to_sql_with_nugen.ipynb`.
