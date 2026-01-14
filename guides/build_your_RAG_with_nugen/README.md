

In order to store the embeddings, we will use Qdrant vector database.


### Setup and Configuration
Importing Libraries and Environment Setup


```python
!pip install --quiet pymupdf==1.24.13 qdrant-client==1.9.1

import hashlib
import requests
import fitz  # PyMuPDF
from qdrant_client import QdrantClient
from qdrant_client.models import PointStruct, VectorParams

```

**Explanation:**

* **request** : For making API calls to Nugen's language model and embedding API.
* **fitz (PyMuPDF):** A library for reading and extracting text from PDF files.

* **QdrantClient:** A client to connect and interact with the Qdrant vector database, where embeddings are stored.
* **dotenv:** Loads environment variables from a .env file to securely manage API keys and database URLs.

* **chainlit:** Used to interact with users and manage messages within a chat-like interface.










### **Defining Global Variables and Model Configuration**

To get started with Nugen APIs, you'll need your Nugen API key, which you can obtain for free. To access free API keys, you can visit [Nugen Dashboard](https://nugen-platform-frontend.azurewebsites.net/dashboard) Nugen offers free access to its powerful AI models, allowing you to integrate features like embeddings and language model completions at no cost.

By leveraging this API key, you can seamlessly integrate Nugen’s cutting-edge APIs into your applications and start building advanced AI-powered solutions right away!




```python

NUGEN_API_KEY = <enter your api key> # Replace with your actual API key from Nugen
LLM_API_URL = "https://api.nugen.cloud/inference"
model_llm = "nugen-flash-instruct"
model_embed = "nugen-flash-embed"
EMBED_DIMENSION = 768
EMBED_CHUNK_SIZE = int(EMBED_DIMENSION * 0.95)
EMBED_CHUNK_OVERLAP = int(EMBED_CHUNK_SIZE * 0.10)
LLM_API_PROVIDER_KEY = NUGEN_API_KEY

# Setup local Qdrant database
qdrant_client = QdrantClient(":memory:")
collection_name = "pdf_embeddings"
top_k = 5

```

### **USE API PROVIDER:**

This variable determines which provider's API will be used. In this case, it is set to "NUGEN", so all API calls are directed to Nugen’s services.

### Nugen API Configuration:


*   **NUGEN_API_KEY:** API key for **Nugen's domain-aligned model services**.
*   **LLM_API_URL:** The base url for Nugen’s large language model inference API.
*   **model_llm and model_embed:** These specify which models to use for generating instruction-based completion and text embeddings, respectively.
      
        1. model_llm: nugen-flash-instruct (used for answering user queries).
        2. model_embed: nugen-flash-embed (used for generating embeddings from text).
    
### Embedding Parameters:

*   **EMBED_DIMENSION:** Dimension of the embedding vector (768 for Nugen's embeddings).

*  **EMBED_CHUNK_SIZE and EMBED_CHUNK_OVERLAP:** This is a parameter to create chunks the contents of the PDF, which is then used to create chunk embeddings. A chunk is the amount of text processed together, and overlap ensures continuity between adjacent chunks.

**QdrantClient:** The client object for connecting to Qdrant (the vector database where embeddings are stored). We use a local instantiation of the qdrant client. Note: If the jupyter notebook kernel is closed, you would need to re-index the embeddings. This is not recommended for working with larger documents. We strongly recommend following the documentation of Qdrant for local deployment here: https://qdrant.tech/documentation/quickstart/. To enable local persistance of the data. For production use cases, please refer to the official documentation here: https://qdrant.tech/documentation/guides/installation/

**Collection Name:** This is the name of the Qdrant collection where embeddings related to the PDFs will be stored.

**top_k:** Defines the number of top results to retrieve from the Qdrant database when searching for relevant context based on the user query.


### **Setting up the Qdrant Collection**

Now, let’s set up the Qdrant collection where the PDF embeddings will be stored. This function checks if the collection already exists, and if not, it creates a new one.


```python
def setup_qdrant_collection(qdrant_client, collection_name, embed_dim):
    try:
        collections = qdrant_client.get_collections().collections
        if collection_name not in [collection.name for collection in collections]:
            qdrant_client.create_collection(
                collection_name=collection_name,
                vectors_config=VectorParams(size=embed_dim, distance="Cosine")
            )
            print(f"Collection '{collection_name}' created.")
        else:
            print(f"Collection '{collection_name}' already exists.")
    except Exception as e:
        print(f"Error setting up Qdrant collection: {e}")
```

* This function checks if a collection (i.e., a "bucket" for storing embeddings) already exists in Qdrant.

* If the collection does not exist, it creates a new one with vector size (embed_dim) based on the embedding dimensions of the Nugen model.

* Cosine distance is used as the metric for comparing vectors, which is standard for similarity searches.






### **Extracting Text and Splitting PDF into Chunks**

The next step is to extract text from a PDF file and split it into manageable chunks. These chunks will then be converted into embeddings.


```python
# 2. Convert PDF to text chunks
def pdf_to_text_chunks(pdf_path, chunk_size, overlap_size):
    doc = fitz.open(pdf_path)
    text = "".join([page.get_text() for page in doc])
    chunks = [text[i:i+chunk_size] for i in range(0, len(text), chunk_size-overlap_size)]
    print(f"Extracted Text: {text[:500]}...")  # Print a sample of extracted text
    print(f"Total chunks created: {len(chunks)}")
    return text, chunks

```

* This function opens the PDF using PyMuPDF (fitz) and extracts all the text from each page of the document.
* Read more about pymupdf [here](https://pymupdf.readthedocs.io/)
* The entire text is then split into chunks of a specific size (chunk_size) with some overlap (overlap_size). Overlapping chunks help maintain continuity in embeddings, which can improve retrieval performance.



### **Generating Embeddings for Text Chunks**

Now that we have text chunks, we need to generate embeddings for them using the Nugen API. This function takes each chunk and sends it to the Nugen API to generate an embedding.

To read more about Nugen API and access free API keys, you can visit [Nugen Intelligence](https://docs.nugen.in/introduction)


```python
# 3. Create embedding using Nugen API
def create_embedding(text, model_embed):
    url = f"{LLM_API_URL}/embeddings"
    headers = {
        "Authorization": f"Bearer {LLM_API_PROVIDER_KEY}",
        "Content-Type": "application/json"
    }
    data = {
        "model": model_embed,
        "input": text
    }
    response = requests.post(url, json=data, headers=headers)
    if response.status_code == 200:
        embedding = response.json()['data'][0]['embedding']
        print(f"Embedding generated for text (length {len(text)}): {embedding[:5]}...")
        return embedding
    else:
        print(f"Error in embedding API: {response.status_code}, {response.text}")
        return None

```

* This function calls the Nugen embedding API to generate embeddings for the given text.
* It sends a POST request to Nugen’s /embeddings endpoint with the text data and embedding model (model_embed).
* The function returns the vector embedding of the text, which is later stored in Qdrant DB.






### **Storing Embeddings in Qdrant DB**

Once embeddings are generated, we need to store them in Qdrant DB. This function handles the creation of points and uploads them to the Qdrant collection.


```python
# 4. Store embeddings in Qdrant
def store_embeddings(chunks, file_path, file_hash, user_id, thread_id, message_id, collection_name):
    try:
        points = []
        for id, chunk in enumerate(chunks):
            embedding = create_embedding(chunk, model_embed)
            if embedding:  # Proceed only if embedding was successfully generated
                points.append(PointStruct(
                    id=id, 
                    vector=embedding, 
                    payload={
                        "chunk_text": chunk,
                        "file_path": file_path,
                        "file_hash": file_hash,
                        "user_id": user_id,
                        "thread_id": thread_id,
                        "message_id": message_id
                    }
                ))
        qdrant_client.upsert(collection_name=collection_name, points=points)
        print(f"Embeddings stored successfully. Total points: {len(points)}")
    except Exception as e:
        print(f"Error storing embeddings: {e}")

```

The above function chunks the input text and stores each chunk's embedding in Qdrant DB.

* Key Parameters:
  * chunks: List of text chunks to be embedded
  * file_path: Path of the file being processed
  * file_hash: Hash of the file to uniquely identify it
  * collection_name: Qdrant collection to store the embeddings

* Return: No return value, but embeddings are upserted (inserted or updated) into Qdrant.

### **Retrieving Relevant Context from Qdrant DB**

Once the embeddings are stored, we can query Qdrant DB to retrieve the most relevant chunks based on a user's query. This function generates an embedding for the query and searches for similar embeddings in Qdrant DB.


```python
# 5. Retrieve relevant chunks based on query
from qdrant_client.models import Filter, FieldCondition, models

def simple_rag_retrieve(query, top_k, user_id, thread_id, collection_name):
    try:
        query_embedding = create_embedding(query, model_embed)

        # Apply the filter with user_id and thread_id to the query
        user_query_filter = Filter(
            must=[
                FieldCondition(key="user_id", match=models.MatchValue(value=user_id)),
                FieldCondition(key="thread_id", match=models.MatchValue(value=thread_id))
            ]
        )

        search_result = qdrant_client.search(
            collection_name=collection_name,
            query_vector=query_embedding,
            limit=top_k,
            query_filter=user_query_filter
        )

        retrieved_texts = [hit.payload['chunk_text'] for hit in search_result]
        print(f"Search Results: {retrieved_texts[:2]}...")  # Show a sample of results
        return "\n".join(retrieved_texts)
    except Exception as e:
        print(f"Error retrieving context: {e}")
        return None

```

**Search**: We use the query embedding to search for the most relevant chunks in the Qdrant collection.

**Filters**: The filter ensures that the results are specific to a particular user and thread.

### **Generate Response Using Nugen API**

After retrieving the relevant text chunks, we can use the Nugen API to generate a response based on the context and user query.


```python
# 6. Generate response using Nugen API
def generate_llm_response(context, query, model_llm):
    url = f"{LLM_API_URL}/chat/completions"
    headers = {
        "Authorization": f"Bearer {LLM_API_PROVIDER_KEY}",
        "Content-Type": "application/json"
    }
    data = {
        "model": model_llm,
        "messages": [
            {"role": "system", "content": "You are a helpful assistant."},
            {"role": "user", "content": f"Context: {context}"},
            {"role": "user", "content": f"Answer the question: {query}"}
        ]
    }
    response = requests.post(url, json=data, headers=headers)
    if response.status_code == 200:
        response_text = response.json()["choices"][0]["message"]["content"]
        print(f"LLM Response: {response_text}")
        return response_text
    else:
        print(f"Error in LLM API: {response.status_code}, {response.text}")
        return None

```

* This function sends a POST request to the Nugen API to generate a response based on the retrieved context and the user query.

* **messages:** The request includes the context retrieved from Qdrant and the user’s query. The assistant uses these messages to generate a relevant response.

* The Nugen API processes this request and returns a completion (answer) that is sent back to the user.






```python
# 7. End-to-end PDF embedding and querying
def embed_pdf_and_query(file_path, user_query, user_id, thread_id, message_id, collection_name):
    # Embed the PDF
    print(f"Processing PDF: {file_path}")
    _, chunks = pdf_to_text_chunks(file_path, EMBED_CHUNK_SIZE, EMBED_CHUNK_OVERLAP)
    file_hash = hashlib.md5(open(file_path, 'rb').read()).hexdigest()
    store_embeddings(chunks, file_path, file_hash, user_id, thread_id, message_id, collection_name)
    
    # Retrieve relevant context for the query
    context = simple_rag_retrieve(user_query, top_k, user_id, thread_id, collection_name)
    
    # Generate response using the context and the query
    if context:
        return generate_llm_response(context, user_query, model_llm)
    else:
        print("No relevant context found.")
        return None

# Example of usage
file_path = "registration_act_1908.pdf"  # Replace with actual PDF file path
user_query = "What are the main conclusions of this document?"
user_id = "user123"
thread_id = "thread456"
message_id = "msg789"

setup_qdrant_collection(qdrant_client, collection_name, EMBED_DIMENSION)
response = embed_pdf_and_query(file_path, user_query, user_id, thread_id, message_id, collection_name)

if response:
    print(f"Final Answer: {response}")
```

**Complete Flow**


Finally, we can put everything together into an end-to-end flow that processes a PDF, stores the embeddings, retrieves relevant context, and generates an answer for the user’s query.

By following this structure, the model enables users to upload PDFs, extract meaningful information from them, and ask questions that are answered based on the embedded content in the document. All of this is powered by Nugen’s APIs and the Qdrant vector database for high-quality search and retrieval.




