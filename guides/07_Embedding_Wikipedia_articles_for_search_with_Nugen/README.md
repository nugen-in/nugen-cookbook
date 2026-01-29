## **Nugen Intelligence**
<img src="https://nugen.in/logo.png" alt="Nugen Logo" width="200"/>


# **Generating Embeddings with Nugen API**

This guide demonstrates how to extract Wikipedia articles by category, clean and chunk their content, and generate semantic embeddings using the Nugen Embeddings API. These embeddings can be used for semantic search, retrieval, and recommendation systems.

## 📘 Notebook Overview

**Notebook path:**  
`guides/07_Embedding_Wikipedia_articles_for_search_with_Nugen.ipynb`

This notebook covers:

1. Retrieving Wikipedia articles using category traversal
2. Cleaning and structuring article sections
3. Token-aware chunking for embedding models
4. Generating embeddings using the Nugen Embeddings API
5. Exporting embeddings for downstream semantic search tasks

**1. Setup**

## Install Required Libraries
We'll install the required Python libraries to interact with Wikipedia, split sections, and count tokens.


```python
!pip install mwclient mwparserfromhell pandas tiktoken openai
```

    Collecting mwclient
      Downloading mwclient-0.11.0-py3-none-any.whl.metadata (3.7 kB)
    Collecting mwparserfromhell
      Downloading mwparserfromhell-0.6.6-cp310-cp310-manylinux_2_17_x86_64.manylinux2014_x86_64.whl.metadata (9.3 kB)
    Requirement already satisfied: pandas in /usr/local/lib/python3.10/dist-packages (2.1.4)
    Collecting tiktoken
      Downloading tiktoken-0.7.0-cp310-cp310-manylinux_2_17_x86_64.manylinux2014_x86_64.whl.metadata (6.6 kB)
    Collecting openai
      Downloading openai-1.46.0-py3-none-any.whl.metadata (24 kB)
    Requirement already satisfied: requests-oauthlib in /usr/local/lib/python3.10/dist-packages (from mwclient) (1.3.1)
    Requirement already satisfied: numpy<2,>=1.22.4 in /usr/local/lib/python3.10/dist-packages (from pandas) (1.26.4)
    Requirement already satisfied: python-dateutil>=2.8.2 in /usr/local/lib/python3.10/dist-packages (from pandas) (2.8.2)
    Requirement already satisfied: pytz>=2020.1 in /usr/local/lib/python3.10/dist-packages (from pandas) (2024.2)
    Requirement already satisfied: tzdata>=2022.1 in /usr/local/lib/python3.10/dist-packages (from pandas) (2024.1)
    Requirement already satisfied: regex>=2022.1.18 in /usr/local/lib/python3.10/dist-packages (from tiktoken) (2024.5.15)
    Requirement already satisfied: requests>=2.26.0 in /usr/local/lib/python3.10/dist-packages (from tiktoken) (2.32.3)
    Requirement already satisfied: anyio<5,>=3.5.0 in /usr/local/lib/python3.10/dist-packages (from openai) (3.7.1)
    Requirement already satisfied: distro<2,>=1.7.0 in /usr/lib/python3/dist-packages (from openai) (1.7.0)
    Collecting httpx<1,>=0.23.0 (from openai)
      Downloading httpx-0.27.2-py3-none-any.whl.metadata (7.1 kB)
    Collecting jiter<1,>=0.4.0 (from openai)
      Downloading jiter-0.5.0-cp310-cp310-manylinux_2_17_x86_64.manylinux2014_x86_64.whl.metadata (3.6 kB)
    Requirement already satisfied: pydantic<3,>=1.9.0 in /usr/local/lib/python3.10/dist-packages (from openai) (2.9.1)
    Requirement already satisfied: sniffio in /usr/local/lib/python3.10/dist-packages (from openai) (1.3.1)
    Requirement already satisfied: tqdm>4 in /usr/local/lib/python3.10/dist-packages (from openai) (4.66.5)
    Requirement already satisfied: typing-extensions<5,>=4.11 in /usr/local/lib/python3.10/dist-packages (from openai) (4.12.2)
    Requirement already satisfied: idna>=2.8 in /usr/local/lib/python3.10/dist-packages (from anyio<5,>=3.5.0->openai) (3.8)
    Requirement already satisfied: exceptiongroup in /usr/local/lib/python3.10/dist-packages (from anyio<5,>=3.5.0->openai) (1.2.2)
    Requirement already satisfied: certifi in /usr/local/lib/python3.10/dist-packages (from httpx<1,>=0.23.0->openai) (2024.8.30)
    Collecting httpcore==1.* (from httpx<1,>=0.23.0->openai)
      Downloading httpcore-1.0.5-py3-none-any.whl.metadata (20 kB)
    Collecting h11<0.15,>=0.13 (from httpcore==1.*->httpx<1,>=0.23.0->openai)
      Downloading h11-0.14.0-py3-none-any.whl.metadata (8.2 kB)
    Requirement already satisfied: annotated-types>=0.6.0 in /usr/local/lib/python3.10/dist-packages (from pydantic<3,>=1.9.0->openai) (0.7.0)
    Requirement already satisfied: pydantic-core==2.23.3 in /usr/local/lib/python3.10/dist-packages (from pydantic<3,>=1.9.0->openai) (2.23.3)
    Requirement already satisfied: six>=1.5 in /usr/local/lib/python3.10/dist-packages (from python-dateutil>=2.8.2->pandas) (1.16.0)
    Requirement already satisfied: charset-normalizer<4,>=2 in /usr/local/lib/python3.10/dist-packages (from requests>=2.26.0->tiktoken) (3.3.2)
    Requirement already satisfied: urllib3<3,>=1.21.1 in /usr/local/lib/python3.10/dist-packages (from requests>=2.26.0->tiktoken) (2.0.7)
    Requirement already satisfied: oauthlib>=3.0.0 in /usr/local/lib/python3.10/dist-packages (from requests-oauthlib->mwclient) (3.2.2)
    Downloading mwclient-0.11.0-py3-none-any.whl (33 kB)
    Downloading mwparserfromhell-0.6.6-cp310-cp310-manylinux_2_17_x86_64.manylinux2014_x86_64.whl (191 kB)
    [2K   [90m━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━[0m [32m191.0/191.0 kB[0m [31m6.0 MB/s[0m eta [36m0:00:00[0m
    [?25hDownloading tiktoken-0.7.0-cp310-cp310-manylinux_2_17_x86_64.manylinux2014_x86_64.whl (1.1 MB)
    [2K   [90m━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━[0m [32m1.1/1.1 MB[0m [31m19.8 MB/s[0m eta [36m0:00:00[0m
    [?25hDownloading openai-1.46.0-py3-none-any.whl (375 kB)
    [2K   [90m━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━[0m [32m375.0/375.0 kB[0m [31m17.2 MB/s[0m eta [36m0:00:00[0m
    [?25hDownloading httpx-0.27.2-py3-none-any.whl (76 kB)
    [2K   [90m━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━[0m [32m76.4/76.4 kB[0m [31m4.9 MB/s[0m eta [36m0:00:00[0m
    [?25hDownloading httpcore-1.0.5-py3-none-any.whl (77 kB)
    [2K   [90m━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━[0m [32m77.9/77.9 kB[0m [31m5.7 MB/s[0m eta [36m0:00:00[0m
    [?25hDownloading jiter-0.5.0-cp310-cp310-manylinux_2_17_x86_64.manylinux2014_x86_64.whl (318 kB)
    [2K   [90m━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━[0m [32m318.9/318.9 kB[0m [31m17.7 MB/s[0m eta [36m0:00:00[0m
    [?25hDownloading h11-0.14.0-py3-none-any.whl (58 kB)
    [2K   [90m━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━[0m [32m58.3/58.3 kB[0m [31m4.0 MB/s[0m eta [36m0:00:00[0m
    [?25hInstalling collected packages: mwparserfromhell, jiter, h11, tiktoken, httpcore, mwclient, httpx, openai
    Successfully installed h11-0.14.0 httpcore-1.0.5 httpx-0.27.2 jiter-0.5.0 mwclient-0.11.0 mwparserfromhell-0.6.6 openai-1.46.0 tiktoken-0.7.0


# **Import Necessary Libraries**
These libraries help us work with Wikipedia articles, clean and process them, and prepare them for embedding using the Nugen API.


```python
import mwclient
import mwparserfromhell
import os
import pandas as pd
import re

import random
import requests
import tiktoken
```

# **2. Access the Nugen API**

## API Key Setup

First, we need to set up the Nugen API to generate embeddings. You’ll need an API key from Nugen, which should be stored as an environment variable. You can replace <your_api_key> with your actual key.


```python
url_api_server = "https://api.nugen.in/inference/embeddings"
api_key = <GET YOUR NUGEN API KEYS>  # Replace with your API key
headers = {
    "Authorization": f"Bearer {api_key}",
    "Content-Type": "application/json"
}

```

    /usr/local/lib/python3.10/dist-packages/ipykernel/ipkernel.py:283: DeprecationWarning: `should_run_async` will not call `transform_cell` automatically in the future. Please pass the result to `transformed_cell` argument and any exception that happen during thetransform in `preprocessing_exc_tuple` in IPython 7.17 and above.
      and should_run_async(code)


# **3. Get Wikipedia Articles**

## **Choosing Wikipedia Articles**

We are going to retrieve articles related to the 2022 Winter Olympics using a Wikipedia category. This section searches for all pages within that category.


```python
CATEGORY_TITLE = "Category:2022 Winter Olympics"
WIKI_SITE = "en.wikipedia.org"
```

    /usr/local/lib/python3.10/dist-packages/ipykernel/ipkernel.py:283: DeprecationWarning: `should_run_async` will not call `transform_cell` automatically in the future. Please pass the result to `transformed_cell` argument and any exception that happen during thetransform in `preprocessing_exc_tuple` in IPython 7.17 and above.
      and should_run_async(code)


## **Extract Article Titles**

We now gather all the article titles under this category.



```python
def titles_from_category(category, max_depth):
    """Return a set of page titles in a given Wiki category and its subcategories."""
    titles = set()
    for cm in category.members():
        if type(cm) == mwclient.page.Page:
            titles.add(cm.name)
        elif isinstance(cm, mwclient.listing.Category) and max_depth > 0:
            deeper_titles = titles_from_category(cm, max_depth=max_depth - 1)
            titles.update(deeper_titles)
    return titles

# Initialize the Wikipedia client
site = mwclient.Site(WIKI_SITE)
category_page = site.pages[CATEGORY_TITLE]
titles = titles_from_category(category_page, max_depth=1)

# Select 20% of the articles for processing
sample_size = int(0.2 * len(titles))
sampled_titles = random.sample(list(titles), sample_size)
print(f"Selected {len(sampled_titles)} article titles for processing.")
```

    /usr/local/lib/python3.10/dist-packages/ipykernel/ipkernel.py:283: DeprecationWarning: `should_run_async` will not call `transform_cell` automatically in the future. Please pass the result to `transformed_cell` argument and any exception that happen during thetransform in `preprocessing_exc_tuple` in IPython 7.17 and above.
      and should_run_async(code)


    Selected 35 article titles for processing.


## **How It Works**

  1. **titles_from_category Function**: This function takes a Wikipedia category and retrieves all article titles within that category and its subcategories up to a specified depth.

  2. **max_depth Parameter:** Controls how deep the function will go into subcategories.

# **4. Chunk Documents**
Now that we have our reference documents, we need to prepare them for search.

For this specific example on Wikipedia articles, we'll:

1. Discard less relevant-looking sections like External Links and Footnotes
2. Clean up the text by removing reference tags (e.g., ), whitespace, and super short sections
3. Split each article into sections
4. Prepend titles and subtitles to each section's text, to help GPT understand the context
5. If a section is long (say, > 1,600 tokens), we'll recursively split it into smaller sections, trying to split along semantic boundaries like paragraphs


```python
SECTIONS_TO_IGNORE = [
    "See also",
    "References",
    "External links",
    "Further reading",
    "Footnotes",
    "Bibliography",
    "Sources",
    "Citations",
    "Literature",
    "Footnotes",
    "Notes and references",
    "Photo gallery",
    "Works cited",
    "Photos",
    "Gallery",
    "Notes",
    "References and sources",
    "References and notes",
]

def all_subsections_from_section(section, parent_titles, sections_to_ignore):
    """Extract subsections from a Wikipedia section."""
    headings = [str(h) for h in section.filter_headings()]
    title = headings[0]
    if title.strip("=" + " ") in sections_to_ignore:
        return []
    titles = parent_titles + [title]
    full_text = str(section)
    section_text = full_text.split(title)[1]
    if len(headings) == 1:
        return [(titles, section_text)]
    else:
        first_subtitle = headings[1]
        section_text = section_text.split(first_subtitle)[0]
        results = [(titles, section_text)]
        for subsection in section.get_sections(levels=[len(titles) + 1]):
            results.extend(all_subsections_from_section(subsection, titles, sections_to_ignore))
        return results
```

    /usr/local/lib/python3.10/dist-packages/ipykernel/ipkernel.py:283: DeprecationWarning: `should_run_async` will not call `transform_cell` automatically in the future. Please pass the result to `transformed_cell` argument and any exception that happen during thetransform in `preprocessing_exc_tuple` in IPython 7.17 and above.
      and should_run_async(code)



```python
def all_subsections_from_title(
    title: str,
    sections_to_ignore: set[str] = SECTIONS_TO_IGNORE,
    site_name: str = WIKI_SITE,
) -> list[tuple[list[str], str]]:
    """From a Wikipedia page title, return a flattened list of all nested subsections.
    Each subsection is a tuple, where:
        - the first element is a list of parent subtitles, starting with the page title
        - the second element is the text of the subsection (but not any children)
    """
    site = mwclient.Site(site_name)
    page = site.pages[title]
    text = page.text()
    parsed_text = mwparserfromhell.parse(text)
    headings = [str(h) for h in parsed_text.filter_headings()]
    if headings:
        summary_text = str(parsed_text).split(headings[0])[0]
    else:
        summary_text = str(parsed_text)
    results = [([title], summary_text)]
    for subsection in parsed_text.get_sections(levels=[2]):
        results.extend(all_subsections_from_section(subsection, [title], sections_to_ignore))
    return results

```

    /usr/local/lib/python3.10/dist-packages/ipykernel/ipkernel.py:283: DeprecationWarning: `should_run_async` will not call `transform_cell` automatically in the future. Please pass the result to `transformed_cell` argument and any exception that happen during thetransform in `preprocessing_exc_tuple` in IPython 7.17 and above.
      and should_run_async(code)


This function splits the articles into smaller sections.

## **Clean Up Sections**
We clean the sections to remove unnecessary information, such as reference tags (<ref>).


```python
wikipedia_sections = []
for title in titles:
    wikipedia_sections.extend(all_subsections_from_title(title))
print(f"Found {len(wikipedia_sections)} sections in {len(titles)} pages.")

# clean text
def clean_section(section: tuple[list[str], str]) -> tuple[list[str], str]:
    """
    Return a cleaned up section with:
        - <ref>xyz</ref> patterns removed
        - leading/trailing whitespace removed
    """
    titles, text = section
    text = re.sub(r"<ref.*?</ref>", "", text)
    text = text.strip()
    return (titles, text)


wikipedia_sections = [clean_section(ws) for ws in wikipedia_sections]

# Filter out short/blank sections
def keep_section(section: tuple[list[str], str]) -> bool:
    titles, text = section
    return len(text) >= 16


original_num_sections = len(wikipedia_sections)
wikipedia_sections = [ws for ws in wikipedia_sections if keep_section(ws)]
print(f"Filtered out {original_num_sections-len(wikipedia_sections)} sections, leaving {len(wikipedia_sections)} sections.")

# Display example data
for ws in wikipedia_sections[:5]:
    print(ws[0])
    print(ws[1][:77] + "...")
    print()

```

    /usr/local/lib/python3.10/dist-packages/ipykernel/ipkernel.py:283: DeprecationWarning: `should_run_async` will not call `transform_cell` automatically in the future. Please pass the result to `transformed_cell` argument and any exception that happen during thetransform in `preprocessing_exc_tuple` in IPython 7.17 and above.
      and should_run_async(code)


    Found 1841 sections in 179 pages.
    Filtered out 89 sections, leaving 1752 sections.
    ['Haiti at the 2022 Winter Olympics']
    {{infobox country at games
    |NOC = HAI
    |NOCname = Comité Olympique Haïtien
    |ga...
    
    ['Haiti at the 2022 Winter Olympics', '==Competitors==']
    The following is the list of number of competitors at the Games per sport/dis...
    
    ['Haiti at the 2022 Winter Olympics', '==Alpine skiing==']
    {{main article|Alpine skiing at the 2022 Winter Olympics|Alpine skiing at the...
    
    ['Alpine skiing at the 2022 Winter Olympics']
    {{short description|Sport at the 2022 Winter Olympic}}
    {{Use dmy dates|date=F...
    
    ['Alpine skiing at the 2022 Winter Olympics', '==Qualification==']
    {{Main|Alpine skiing at the 2022 Winter Olympics – Qualification}}
    A maximum ...
    


# **5. Handle Text Length (Tokens)**
Embeddings work best when the text is not too long. We count the tokens (words and characters) to ensure that each section is short enough.


```python
GPT_MODEL = "gpt-3.5-turbo"  # Just to select tokenizer, does not use OpenAI models

def num_tokens(text: str, model: str = GPT_MODEL) -> int:
    encoding = tiktoken.encoding_for_model(model)
    return len(encoding.encode(text))

def halved_by_delimiter(string: str, delimiter: str = "\n") -> list[str, str]:
    chunks = string.split(delimiter)
    if len(chunks) == 1:
        return [string, ""]
    total_tokens = num_tokens(string)
    halfway = total_tokens // 2
    best_diff = halfway
    for i, chunk in enumerate(chunks):
        left = delimiter.join(chunks[: i + 1])
        left_tokens = num_tokens(left)
        diff = abs(halfway - left_tokens)
        if diff >= best_diff:
            break
        best_diff = diff
    left = delimiter.join(chunks[:i])
    right = delimiter.join(chunks[i:])
    return [left, right]

def truncated_string(string: str, model: str, max_tokens: int, print_warning: bool = True) -> str:
    encoding = tiktoken.encoding_for_model(model)
    encoded_string = encoding.encode(string)
    truncated_string = encoding.decode(encoded_string[:max_tokens])
    if print_warning and len(encoded_string) > max_tokens:
        print(f"Warning: Truncated string from {len(encoded_string)} tokens to {max_tokens} tokens.")
    return truncated_string

def split_strings_from_subsection(subsection: tuple[list[str], str], max_tokens: int = 1000, model: str = GPT_MODEL, max_recursion: int = 5) -> list[str]:
    titles, text = subsection
    string = "\n\n".join(titles + [text])
    if num_tokens(string) <= max_tokens:
        return [string]
    elif max_recursion == 0:
        return [truncated_string(string, model=model, max_tokens=max_tokens)]
    for delimiter in ["\n\n", "\n", ". "]:
        left, right = halved_by_delimiter(text, delimiter=delimiter)
        if left == "" or right == "":
            continue
        results = []
        for half in [left, right]:
            half_subsection = (titles, half)
            half_strings = split_strings_from_subsection(
                half_subsection,
                max_tokens=max_tokens,
                model=model,
                max_recursion=max_recursion - 1,
            )
            results.extend(half_strings)
        return results
    return [truncated_string(string, model=model, max_tokens=max_tokens)]

# Split sections into chunks
MAX_TOKENS = 1600
wikipedia_strings = []
for section in wikipedia_sections:
    wikipedia_strings.extend(split_strings_from_subsection(section, max_tokens=MAX_TOKENS))

print(f"{len(wikipedia_sections)} Wikipedia sections split into {len(wikipedia_strings)} strings.")
```

    /usr/local/lib/python3.10/dist-packages/ipykernel/ipkernel.py:283: DeprecationWarning: `should_run_async` will not call `transform_cell` automatically in the future. Please pass the result to `transformed_cell` argument and any exception that happen during thetransform in `preprocessing_exc_tuple` in IPython 7.17 and above.
      and should_run_async(code)


    1752 Wikipedia sections split into 2062 strings.


# **6. Generate Embeddings**
## **Prepare Text for Embedding**
After splitting the sections, we convert them into numerical values (embeddings). These embeddings help computers understand the content.


```python
# Split sections into chunks
MAX_TOKENS = 1600
wikipedia_strings = []
for section in wikipedia_sections:
    wikipedia_strings.extend(split_strings_from_subsection(section, max_tokens=MAX_TOKENS))

print(f"{len(wikipedia_sections)} Wikipedia sections split into {len(wikipedia_strings)} strings.")

# Fetch embeddings from Nugen API
BATCH_SIZE = 100
EMBEDDING_MODEL = "nugen-flash-embed"
embeddings = []

for batch_start in range(0, len(wikipedia_strings), BATCH_SIZE):
    batch_end = batch_start + BATCH_SIZE
    batch = wikipedia_strings[batch_start:batch_end]
    print(f"Processing batch {batch_start} to {batch_end-1}")

    payload = {
        "input": batch,
        "model": EMBEDDING_MODEL
    }

    try:
        response = requests.post(url_api_server, json=payload, headers=headers)
        response.raise_for_status()
        data = response.json()
        batch_embeddings = [e['embedding'] for e in data['data']]
        embeddings.extend(batch_embeddings)
    except requests.exceptions.HTTPError as e:
        print(f"HTTP error occurred: {e}")
        print(f"Response content: {response.content}")
    except Exception as e:
        print(f"An error occurred: {e}")
```

    /usr/local/lib/python3.10/dist-packages/ipykernel/ipkernel.py:283: DeprecationWarning: `should_run_async` will not call `transform_cell` automatically in the future. Please pass the result to `transformed_cell` argument and any exception that happen during thetransform in `preprocessing_exc_tuple` in IPython 7.17 and above.
      and should_run_async(code)


    1752 Wikipedia sections split into 2062 strings.
    Processing batch 0 to 99
    Processing batch 100 to 199
    Processing batch 200 to 299
    Processing batch 300 to 399
    Processing batch 400 to 499
    Processing batch 500 to 599
    Processing batch 600 to 699
    Processing batch 700 to 799
    Processing batch 800 to 899
    Processing batch 900 to 999
    Processing batch 1000 to 1099
    Processing batch 1100 to 1199
    Processing batch 1200 to 1299
    Processing batch 1300 to 1399
    Processing batch 1400 to 1499
    Processing batch 1500 to 1599
    Processing batch 1600 to 1699
    Processing batch 1700 to 1799
    Processing batch 1800 to 1899
    Processing batch 1900 to 1999
    Processing batch 2000 to 2099


# **7. Save the Results**


```python
# Save the embeddings
df = pd.DataFrame({"text": wikipedia_strings, "embedding": embeddings})
SAVE_PATH = "winter_olympics_2022.csv"
df.to_csv(SAVE_PATH, index=False)
print(f"Embeddings saved to {SAVE_PATH}")
```

    /usr/local/lib/python3.10/dist-packages/ipykernel/ipkernel.py:283: DeprecationWarning: `should_run_async` will not call `transform_cell` automatically in the future. Please pass the result to `transformed_cell` argument and any exception that happen during thetransform in `preprocessing_exc_tuple` in IPython 7.17 and above.
      and should_run_async(code)


    Embeddings saved to winter_olympics_2022.csv



```python

```

## Improvements Added

- Improved documentation clarity and narrative flow across the notebook
- Added safeguards for empty, short, or malformed Wikipedia sections
- Enhanced chunking logic to better respect semantic boundaries
- Clarified secure API usage using environment variables
- Improved readability and maintainability with structured comments
