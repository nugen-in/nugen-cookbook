## **Nugen Intelligence**
<img src="https://nugen.in/logo.png" alt="Nugen Logo" width="200"/>

# Chat-with-PDF using Nugen APIs
---

This documentation explains the implementation of a chat-with-PDF functionality, where PDF documents are embedded into a vector database, and queries are answered based on contextual search from these embeddings. The code uses Nugen APIs for generating embeddings and language model completions, and Qdrant as the vector database to store and retrieve these embeddings.


# Setup and Configuration
Importing Libraries and Environment Setup


```python
!pip install pymupdf
!pip install qdrant-client
!pip install chainlit
import os
import sys
import hashlib
import requests
import fitz  # PyMuPDF
from qdrant_client import QdrantClient
from qdrant_client.models import PointStruct, VectorParams
import chainlit as cl

# Load environment variables

```

    Collecting pymupdf
      Downloading PyMuPDF-1.24.10-cp310-none-manylinux2014_x86_64.whl.metadata (3.4 kB)
    Collecting PyMuPDFb==1.24.10 (from pymupdf)
      Downloading PyMuPDFb-1.24.10-py3-none-manylinux2014_x86_64.manylinux_2_17_x86_64.whl.metadata (1.4 kB)
    Downloading PyMuPDF-1.24.10-cp310-none-manylinux2014_x86_64.whl (3.5 MB)
    [2K   [90m━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━[0m [32m3.5/3.5 MB[0m [31m28.5 MB/s[0m eta [36m0:00:00[0m
    [?25hDownloading PyMuPDFb-1.24.10-py3-none-manylinux2014_x86_64.manylinux_2_17_x86_64.whl (15.9 MB)
    [2K   [90m━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━[0m [32m15.9/15.9 MB[0m [31m82.1 MB/s[0m eta [36m0:00:00[0m
    [?25hInstalling collected packages: PyMuPDFb, pymupdf
    Successfully installed PyMuPDFb-1.24.10 pymupdf-1.24.10
    Collecting qdrant-client
      Downloading qdrant_client-1.11.2-py3-none-any.whl.metadata (10 kB)
    Requirement already satisfied: grpcio>=1.41.0 in /usr/local/lib/python3.10/dist-packages (from qdrant-client) (1.64.1)
    Collecting grpcio-tools>=1.41.0 (from qdrant-client)
      Downloading grpcio_tools-1.66.1-cp310-cp310-manylinux_2_17_x86_64.manylinux2014_x86_64.whl.metadata (5.3 kB)
    Requirement already satisfied: httpx>=0.20.0 in /usr/local/lib/python3.10/dist-packages (from httpx[http2]>=0.20.0->qdrant-client) (0.27.2)
    Requirement already satisfied: numpy>=1.21 in /usr/local/lib/python3.10/dist-packages (from qdrant-client) (1.26.4)
    Collecting portalocker<3.0.0,>=2.7.0 (from qdrant-client)
      Downloading portalocker-2.10.1-py3-none-any.whl.metadata (8.5 kB)
    Requirement already satisfied: pydantic>=1.10.8 in /usr/local/lib/python3.10/dist-packages (from qdrant-client) (2.9.2)
    Requirement already satisfied: urllib3<3,>=1.26.14 in /usr/local/lib/python3.10/dist-packages (from qdrant-client) (2.2.3)
    Collecting protobuf<6.0dev,>=5.26.1 (from grpcio-tools>=1.41.0->qdrant-client)
      Downloading protobuf-5.28.2-cp38-abi3-manylinux2014_x86_64.whl.metadata (592 bytes)
    Collecting grpcio>=1.41.0 (from qdrant-client)
      Downloading grpcio-1.66.1-cp310-cp310-manylinux_2_17_x86_64.manylinux2014_x86_64.whl.metadata (3.9 kB)
    Requirement already satisfied: setuptools in /usr/local/lib/python3.10/dist-packages (from grpcio-tools>=1.41.0->qdrant-client) (71.0.4)
    Requirement already satisfied: anyio in /usr/local/lib/python3.10/dist-packages (from httpx>=0.20.0->httpx[http2]>=0.20.0->qdrant-client) (3.7.1)
    Requirement already satisfied: certifi in /usr/local/lib/python3.10/dist-packages (from httpx>=0.20.0->httpx[http2]>=0.20.0->qdrant-client) (2024.8.30)
    Requirement already satisfied: httpcore==1.* in /usr/local/lib/python3.10/dist-packages (from httpx>=0.20.0->httpx[http2]>=0.20.0->qdrant-client) (1.0.5)
    Requirement already satisfied: idna in /usr/local/lib/python3.10/dist-packages (from httpx>=0.20.0->httpx[http2]>=0.20.0->qdrant-client) (3.10)
    Requirement already satisfied: sniffio in /usr/local/lib/python3.10/dist-packages (from httpx>=0.20.0->httpx[http2]>=0.20.0->qdrant-client) (1.3.1)
    Requirement already satisfied: h11<0.15,>=0.13 in /usr/local/lib/python3.10/dist-packages (from httpcore==1.*->httpx>=0.20.0->httpx[http2]>=0.20.0->qdrant-client) (0.14.0)
    Collecting h2<5,>=3 (from httpx[http2]>=0.20.0->qdrant-client)
      Downloading h2-4.1.0-py3-none-any.whl.metadata (3.6 kB)
    Requirement already satisfied: annotated-types>=0.6.0 in /usr/local/lib/python3.10/dist-packages (from pydantic>=1.10.8->qdrant-client) (0.7.0)
    Requirement already satisfied: pydantic-core==2.23.4 in /usr/local/lib/python3.10/dist-packages (from pydantic>=1.10.8->qdrant-client) (2.23.4)
    Requirement already satisfied: typing-extensions>=4.6.1 in /usr/local/lib/python3.10/dist-packages (from pydantic>=1.10.8->qdrant-client) (4.12.2)
    Collecting hyperframe<7,>=6.0 (from h2<5,>=3->httpx[http2]>=0.20.0->qdrant-client)
      Downloading hyperframe-6.0.1-py3-none-any.whl.metadata (2.7 kB)
    Collecting hpack<5,>=4.0 (from h2<5,>=3->httpx[http2]>=0.20.0->qdrant-client)
      Downloading hpack-4.0.0-py3-none-any.whl.metadata (2.5 kB)
    Requirement already satisfied: exceptiongroup in /usr/local/lib/python3.10/dist-packages (from anyio->httpx>=0.20.0->httpx[http2]>=0.20.0->qdrant-client) (1.2.2)
    Downloading qdrant_client-1.11.2-py3-none-any.whl (258 kB)
    [2K   [90m━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━[0m [32m258.9/258.9 kB[0m [31m7.6 MB/s[0m eta [36m0:00:00[0m
    [?25hDownloading grpcio_tools-1.66.1-cp310-cp310-manylinux_2_17_x86_64.manylinux2014_x86_64.whl (2.4 MB)
    [2K   [90m━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━[0m [32m2.4/2.4 MB[0m [31m47.7 MB/s[0m eta [36m0:00:00[0m
    [?25hDownloading grpcio-1.66.1-cp310-cp310-manylinux_2_17_x86_64.manylinux2014_x86_64.whl (5.7 MB)
    [2K   [90m━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━[0m [32m5.7/5.7 MB[0m [31m84.8 MB/s[0m eta [36m0:00:00[0m
    [?25hDownloading portalocker-2.10.1-py3-none-any.whl (18 kB)
    Downloading h2-4.1.0-py3-none-any.whl (57 kB)
    [2K   [90m━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━[0m [32m57.5/57.5 kB[0m [31m3.7 MB/s[0m eta [36m0:00:00[0m
    [?25hDownloading protobuf-5.28.2-cp38-abi3-manylinux2014_x86_64.whl (316 kB)
    [2K   [90m━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━[0m [32m316.6/316.6 kB[0m [31m22.0 MB/s[0m eta [36m0:00:00[0m
    [?25hDownloading hpack-4.0.0-py3-none-any.whl (32 kB)
    Downloading hyperframe-6.0.1-py3-none-any.whl (12 kB)
    Installing collected packages: protobuf, portalocker, hyperframe, hpack, grpcio, h2, grpcio-tools, qdrant-client
      Attempting uninstall: protobuf
        Found existing installation: protobuf 3.20.3
        Uninstalling protobuf-3.20.3:
          Successfully uninstalled protobuf-3.20.3
      Attempting uninstall: grpcio
        Found existing installation: grpcio 1.64.1
        Uninstalling grpcio-1.64.1:
          Successfully uninstalled grpcio-1.64.1
    [31mERROR: pip's dependency resolver does not currently take into account all the packages that are installed. This behaviour is the source of the following dependency conflicts.
    cudf-cu12 24.4.1 requires protobuf<5,>=3.20, but you have protobuf 5.28.2 which is incompatible.
    google-ai-generativelanguage 0.6.6 requires protobuf!=3.20.0,!=3.20.1,!=4.21.0,!=4.21.1,!=4.21.2,!=4.21.3,!=4.21.4,!=4.21.5,<5.0.0dev,>=3.19.5, but you have protobuf 5.28.2 which is incompatible.
    google-cloud-datastore 2.19.0 requires protobuf!=3.20.0,!=3.20.1,!=4.21.0,!=4.21.1,!=4.21.2,!=4.21.3,!=4.21.4,!=4.21.5,<5.0.0dev,>=3.19.5, but you have protobuf 5.28.2 which is incompatible.
    google-cloud-firestore 2.16.1 requires protobuf!=3.20.0,!=3.20.1,!=4.21.0,!=4.21.1,!=4.21.2,!=4.21.3,!=4.21.4,!=4.21.5,<5.0.0dev,>=3.19.5, but you have protobuf 5.28.2 which is incompatible.
    opentelemetry-proto 1.27.0 requires protobuf<5.0,>=3.19, but you have protobuf 5.28.2 which is incompatible.
    tensorboard 2.17.0 requires protobuf!=4.24.0,<5.0.0,>=3.19.6, but you have protobuf 5.28.2 which is incompatible.
    tensorflow 2.17.0 requires protobuf!=4.21.0,!=4.21.1,!=4.21.2,!=4.21.3,!=4.21.4,!=4.21.5,<5.0.0dev,>=3.20.3, but you have protobuf 5.28.2 which is incompatible.
    tensorflow-metadata 1.15.0 requires protobuf<4.21,>=3.20.3; python_version < "3.11", but you have protobuf 5.28.2 which is incompatible.[0m[31m
    [0mSuccessfully installed grpcio-1.66.1 grpcio-tools-1.66.1 h2-4.1.0 hpack-4.0.0 hyperframe-6.0.1 portalocker-2.10.1 protobuf-5.28.2 qdrant-client-1.11.2
    Requirement already satisfied: chainlit in /usr/local/lib/python3.10/dist-packages (1.2.0)
    Requirement already satisfied: aiofiles<24.0.0,>=23.1.0 in /usr/local/lib/python3.10/dist-packages (from chainlit) (23.2.1)
    Requirement already satisfied: asyncer<0.0.8,>=0.0.7 in /usr/local/lib/python3.10/dist-packages (from chainlit) (0.0.7)
    Requirement already satisfied: click<9.0.0,>=8.1.3 in /usr/local/lib/python3.10/dist-packages (from chainlit) (8.1.7)
    Requirement already satisfied: dataclasses_json<0.7.0,>=0.6.7 in /usr/local/lib/python3.10/dist-packages (from chainlit) (0.6.7)
    Requirement already satisfied: fastapi<0.113,>=0.110.1 in /usr/local/lib/python3.10/dist-packages (from chainlit) (0.112.4)
    Requirement already satisfied: filetype<2.0.0,>=1.2.0 in /usr/local/lib/python3.10/dist-packages (from chainlit) (1.2.0)
    Requirement already satisfied: httpx>=0.23.0 in /usr/local/lib/python3.10/dist-packages (from chainlit) (0.27.2)
    Requirement already satisfied: lazify<0.5.0,>=0.4.0 in /usr/local/lib/python3.10/dist-packages (from chainlit) (0.4.0)
    Requirement already satisfied: literalai==0.0.607 in /usr/local/lib/python3.10/dist-packages (from chainlit) (0.0.607)
    Requirement already satisfied: nest-asyncio<2.0.0,>=1.6.0 in /usr/local/lib/python3.10/dist-packages (from chainlit) (1.6.0)
    Requirement already satisfied: numpy<2.0,>=1.26 in /usr/local/lib/python3.10/dist-packages (from chainlit) (1.26.4)
    Requirement already satisfied: packaging<24.0,>=23.1 in /usr/local/lib/python3.10/dist-packages (from chainlit) (23.2)
    Requirement already satisfied: pydantic<3,>=1 in /usr/local/lib/python3.10/dist-packages (from chainlit) (2.9.2)
    Requirement already satisfied: pyjwt<3.0.0,>=2.8.0 in /usr/local/lib/python3.10/dist-packages (from chainlit) (2.9.0)
    Requirement already satisfied: python-dotenv<2.0.0,>=1.0.0 in /usr/local/lib/python3.10/dist-packages (from chainlit) (1.0.1)
    Requirement already satisfied: python-multipart<0.0.10,>=0.0.9 in /usr/local/lib/python3.10/dist-packages (from chainlit) (0.0.9)
    Requirement already satisfied: python-socketio<6.0.0,>=5.11.0 in /usr/local/lib/python3.10/dist-packages (from chainlit) (5.11.4)
    Requirement already satisfied: starlette<0.38.0,>=0.37.2 in /usr/local/lib/python3.10/dist-packages (from chainlit) (0.37.2)
    Requirement already satisfied: syncer<3.0.0,>=2.0.3 in /usr/local/lib/python3.10/dist-packages (from chainlit) (2.0.3)
    Requirement already satisfied: tomli<3.0.0,>=2.0.1 in /usr/local/lib/python3.10/dist-packages (from chainlit) (2.0.1)
    Requirement already satisfied: uptrace<2.0.0,>=1.22.0 in /usr/local/lib/python3.10/dist-packages (from chainlit) (1.26.0)
    Requirement already satisfied: uvicorn<0.26.0,>=0.25.0 in /usr/local/lib/python3.10/dist-packages (from chainlit) (0.25.0)
    Requirement already satisfied: watchfiles<0.21.0,>=0.20.0 in /usr/local/lib/python3.10/dist-packages (from chainlit) (0.20.0)
    Requirement already satisfied: chevron>=0.14.0 in /usr/local/lib/python3.10/dist-packages (from literalai==0.0.607->chainlit) (0.14.0)
    Requirement already satisfied: anyio<5.0,>=3.4.0 in /usr/local/lib/python3.10/dist-packages (from asyncer<0.0.8,>=0.0.7->chainlit) (3.7.1)
    Requirement already satisfied: marshmallow<4.0.0,>=3.18.0 in /usr/local/lib/python3.10/dist-packages (from dataclasses_json<0.7.0,>=0.6.7->chainlit) (3.22.0)
    Requirement already satisfied: typing-inspect<1,>=0.4.0 in /usr/local/lib/python3.10/dist-packages (from dataclasses_json<0.7.0,>=0.6.7->chainlit) (0.9.0)
    Requirement already satisfied: typing-extensions>=4.8.0 in /usr/local/lib/python3.10/dist-packages (from fastapi<0.113,>=0.110.1->chainlit) (4.12.2)
    Requirement already satisfied: certifi in /usr/local/lib/python3.10/dist-packages (from httpx>=0.23.0->chainlit) (2024.8.30)
    Requirement already satisfied: httpcore==1.* in /usr/local/lib/python3.10/dist-packages (from httpx>=0.23.0->chainlit) (1.0.5)
    Requirement already satisfied: idna in /usr/local/lib/python3.10/dist-packages (from httpx>=0.23.0->chainlit) (3.10)
    Requirement already satisfied: sniffio in /usr/local/lib/python3.10/dist-packages (from httpx>=0.23.0->chainlit) (1.3.1)
    Requirement already satisfied: h11<0.15,>=0.13 in /usr/local/lib/python3.10/dist-packages (from httpcore==1.*->httpx>=0.23.0->chainlit) (0.14.0)
    Requirement already satisfied: annotated-types>=0.6.0 in /usr/local/lib/python3.10/dist-packages (from pydantic<3,>=1->chainlit) (0.7.0)
    Requirement already satisfied: pydantic-core==2.23.4 in /usr/local/lib/python3.10/dist-packages (from pydantic<3,>=1->chainlit) (2.23.4)
    Requirement already satisfied: bidict>=0.21.0 in /usr/local/lib/python3.10/dist-packages (from python-socketio<6.0.0,>=5.11.0->chainlit) (0.23.1)
    Requirement already satisfied: python-engineio>=4.8.0 in /usr/local/lib/python3.10/dist-packages (from python-socketio<6.0.0,>=5.11.0->chainlit) (4.9.1)
    Requirement already satisfied: opentelemetry-api~=1.26 in /usr/local/lib/python3.10/dist-packages (from uptrace<2.0.0,>=1.22.0->chainlit) (1.27.0)
    Requirement already satisfied: opentelemetry-exporter-otlp~=1.26 in /usr/local/lib/python3.10/dist-packages (from uptrace<2.0.0,>=1.22.0->chainlit) (1.27.0)
    Requirement already satisfied: opentelemetry-instrumentation~=0.47b0 in /usr/local/lib/python3.10/dist-packages (from uptrace<2.0.0,>=1.22.0->chainlit) (0.48b0)
    Requirement already satisfied: opentelemetry-sdk~=1.26 in /usr/local/lib/python3.10/dist-packages (from uptrace<2.0.0,>=1.22.0->chainlit) (1.27.0)
    Requirement already satisfied: exceptiongroup in /usr/local/lib/python3.10/dist-packages (from anyio<5.0,>=3.4.0->asyncer<0.0.8,>=0.0.7->chainlit) (1.2.2)
    Requirement already satisfied: deprecated>=1.2.6 in /usr/local/lib/python3.10/dist-packages (from opentelemetry-api~=1.26->uptrace<2.0.0,>=1.22.0->chainlit) (1.2.14)
    Requirement already satisfied: importlib-metadata<=8.4.0,>=6.0 in /usr/local/lib/python3.10/dist-packages (from opentelemetry-api~=1.26->uptrace<2.0.0,>=1.22.0->chainlit) (8.4.0)
    Requirement already satisfied: opentelemetry-exporter-otlp-proto-grpc==1.27.0 in /usr/local/lib/python3.10/dist-packages (from opentelemetry-exporter-otlp~=1.26->uptrace<2.0.0,>=1.22.0->chainlit) (1.27.0)
    Requirement already satisfied: opentelemetry-exporter-otlp-proto-http==1.27.0 in /usr/local/lib/python3.10/dist-packages (from opentelemetry-exporter-otlp~=1.26->uptrace<2.0.0,>=1.22.0->chainlit) (1.27.0)
    Requirement already satisfied: googleapis-common-protos~=1.52 in /usr/local/lib/python3.10/dist-packages (from opentelemetry-exporter-otlp-proto-grpc==1.27.0->opentelemetry-exporter-otlp~=1.26->uptrace<2.0.0,>=1.22.0->chainlit) (1.65.0)
    Requirement already satisfied: grpcio<2.0.0,>=1.0.0 in /usr/local/lib/python3.10/dist-packages (from opentelemetry-exporter-otlp-proto-grpc==1.27.0->opentelemetry-exporter-otlp~=1.26->uptrace<2.0.0,>=1.22.0->chainlit) (1.66.1)
    Requirement already satisfied: opentelemetry-exporter-otlp-proto-common==1.27.0 in /usr/local/lib/python3.10/dist-packages (from opentelemetry-exporter-otlp-proto-grpc==1.27.0->opentelemetry-exporter-otlp~=1.26->uptrace<2.0.0,>=1.22.0->chainlit) (1.27.0)
    Requirement already satisfied: opentelemetry-proto==1.27.0 in /usr/local/lib/python3.10/dist-packages (from opentelemetry-exporter-otlp-proto-grpc==1.27.0->opentelemetry-exporter-otlp~=1.26->uptrace<2.0.0,>=1.22.0->chainlit) (1.27.0)
    Requirement already satisfied: requests~=2.7 in /usr/local/lib/python3.10/dist-packages (from opentelemetry-exporter-otlp-proto-http==1.27.0->opentelemetry-exporter-otlp~=1.26->uptrace<2.0.0,>=1.22.0->chainlit) (2.32.3)
    Collecting protobuf<5.0,>=3.19 (from opentelemetry-proto==1.27.0->opentelemetry-exporter-otlp-proto-grpc==1.27.0->opentelemetry-exporter-otlp~=1.26->uptrace<2.0.0,>=1.22.0->chainlit)
      Downloading protobuf-4.25.5-cp37-abi3-manylinux2014_x86_64.whl.metadata (541 bytes)
    Requirement already satisfied: setuptools>=16.0 in /usr/local/lib/python3.10/dist-packages (from opentelemetry-instrumentation~=0.47b0->uptrace<2.0.0,>=1.22.0->chainlit) (71.0.4)
    Requirement already satisfied: wrapt<2.0.0,>=1.0.0 in /usr/local/lib/python3.10/dist-packages (from opentelemetry-instrumentation~=0.47b0->uptrace<2.0.0,>=1.22.0->chainlit) (1.16.0)
    Requirement already satisfied: opentelemetry-semantic-conventions==0.48b0 in /usr/local/lib/python3.10/dist-packages (from opentelemetry-sdk~=1.26->uptrace<2.0.0,>=1.22.0->chainlit) (0.48b0)
    Requirement already satisfied: simple-websocket>=0.10.0 in /usr/local/lib/python3.10/dist-packages (from python-engineio>=4.8.0->python-socketio<6.0.0,>=5.11.0->chainlit) (1.0.0)
    Requirement already satisfied: mypy-extensions>=0.3.0 in /usr/local/lib/python3.10/dist-packages (from typing-inspect<1,>=0.4.0->dataclasses_json<0.7.0,>=0.6.7->chainlit) (1.0.0)
    Requirement already satisfied: zipp>=0.5 in /usr/local/lib/python3.10/dist-packages (from importlib-metadata<=8.4.0,>=6.0->opentelemetry-api~=1.26->uptrace<2.0.0,>=1.22.0->chainlit) (3.20.2)
    Requirement already satisfied: wsproto in /usr/local/lib/python3.10/dist-packages (from simple-websocket>=0.10.0->python-engineio>=4.8.0->python-socketio<6.0.0,>=5.11.0->chainlit) (1.2.0)
    Requirement already satisfied: charset-normalizer<4,>=2 in /usr/local/lib/python3.10/dist-packages (from requests~=2.7->opentelemetry-exporter-otlp-proto-http==1.27.0->opentelemetry-exporter-otlp~=1.26->uptrace<2.0.0,>=1.22.0->chainlit) (3.3.2)
    Requirement already satisfied: urllib3<3,>=1.21.1 in /usr/local/lib/python3.10/dist-packages (from requests~=2.7->opentelemetry-exporter-otlp-proto-http==1.27.0->opentelemetry-exporter-otlp~=1.26->uptrace<2.0.0,>=1.22.0->chainlit) (2.2.3)
    Downloading protobuf-4.25.5-cp37-abi3-manylinux2014_x86_64.whl (294 kB)
    [2K   [90m━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━[0m [32m294.6/294.6 kB[0m [31m4.9 MB/s[0m eta [36m0:00:00[0m
    [?25hInstalling collected packages: protobuf
      Attempting uninstall: protobuf
        Found existing installation: protobuf 5.28.2
        Uninstalling protobuf-5.28.2:
          Successfully uninstalled protobuf-5.28.2
    [31mERROR: pip's dependency resolver does not currently take into account all the packages that are installed. This behaviour is the source of the following dependency conflicts.
    grpcio-tools 1.66.1 requires protobuf<6.0dev,>=5.26.1, but you have protobuf 4.25.5 which is incompatible.
    tensorflow-metadata 1.15.0 requires protobuf<4.21,>=3.20.3; python_version < "3.11", but you have protobuf 4.25.5 which is incompatible.[0m[31m
    [0mSuccessfully installed protobuf-4.25.5


# **Explanation:**


* **os and sys:** These modules are used to interact with the system environment and handle operations such as reading environment variables and exiting the program.

* **hashlib:** Utilized for generating a unique hash of the PDF files to check for duplicates in the database.

* **request** : For making API calls to Nugen's language model and embedding API.
* **fitz (PyMuPDF):** A library for reading and extracting text from PDF files.

* **QdrantClient:** A client to connect and interact with the Qdrant vector database, where embeddings are stored.
* **dotenv:** Loads environment variables from a .env file to securely manage API keys and database URLs.

* **chainlit:** Used to interact with users and manage messages within a chat-like interface.










## **Defining Global Variables and Model Configuration**




```python
USE_API_PROVIDER = "NUGEN"
if USE_API_PROVIDER == "NUGEN":
    NUGEN_API_KEY = <GET YOUR NUGEN API KEYS>
    LLM_API_URL = "https://api.nugen.in/inference"
    model_llm = "nugen-flash-instruct"
    model_embed = "nugen-flash-embed"
    EMBED_DIMENSION = 768
    EMBED_CHUNK_SIZE = int(EMBED_DIMENSION * 0.95)
    EMBED_CHUNK_OVERLAP = int(EMBED_CHUNK_SIZE * 0.10)
    LLM_API_PROVIDER_KEY = NUGEN_API_KEY
else:
    print("Unexpected USE_API_PROVIDER=", USE_API_PROVIDER)
    sys.exit()

qdrant_client = QdrantClient(os.getenv("QDRANT_CLIENT_URL"))
collection_name = "pdf_embeddings"
top_k = 5

```

# **USE API PROVIDER:**

This variable determines which provider's API will be used. In this case, it is set to "NUGEN", so all API calls are directed to Nugen’s services.

# **Nugen API Configuration:**


*   **NUGEN_API_KEY:** API key for **Nugen's domain-aligned model services**, loaded from environment variables.
*   **LLM_API_URL:** The endpoint for Nugen’s large language model inference API.
*   **model_llm and model_embed:** These specify which models to use for instruction-based completion and text embeddings.
      
        1. model_llm: nugen-flash-instruct (used for answering user queries).
        2. model_embed: nugen-flash-embed (used for generating embeddings from text).
    
# **Embedding Parameters:**

*   **EMBED_DIMENSION:** Dimension of the embedding vector (768 for Nugen's embeddings).

*  **EMBED_CHUNK_SIZE and EMBED_CHUNK_OVERLAP:** These control how PDF content is split into chunks for embedding. A chunk is the amount of text processed together, and overlap ensures continuity between adjacent chunks.

**QdrantClient:** The client object for connecting to Qdrant (the vector database where embeddings are stored). It connects using the URL provided by the environment variable QDRANT_CLIENT_URL.

**Collection Name:** collection_name is set to pdf_embeddings. This is the Qdrant collection where embeddings related to the PDFs will be stored.

**top_k:** Defines the number of top results to retrieve from the Qdrant database when searching for relevant context based on the user query.



# **Setting up the Qdrant Collection**


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






# **Extracting Text and Splitting PDF into Chunks**


```python
def pdf_to_text_chunks(pdf_path, chunk_size, overlap_size):
    doc = fitz.open(pdf_path)
    text = "".join([page.get_text() for page in doc])
    chunks = [text[i:i+chunk_size] for i in range(0, len(text), chunk_size-overlap_size)]
    return text, chunks
```

* This function opens the PDF using PyMuPDF (fitz) and extracts all the text from each page of the document.
* The entire text is then split into chunks of a specific size (chunk_size) with some overlap (overlap_size). Overlapping chunks help maintain continuity in embeddings, which can improve retrieval performance.



# **Generating Embeddings for Text Chunks**


```python
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
    response.raise_for_status()
    return response.json()['data'][0]['embedding']
```

* This function calls the Nugen embedding API to generate embeddings for the given text.
* It sends a POST request to Nugen’s /embeddings endpoint with the text data and embedding model (model_embed).
* The function returns the vector embedding of the text, which is later stored in Qdrant.






# **Storing Embeddings in Qdrant**


```python
def store_embeddings(chunks, file_path, file_hash, user_id, thread_id, message_id, collection_name):
    try:
        points = [
            PointStruct(id=i, vector=create_embedding(chunk, model_embed), payload={
                "chunk_text": chunk, "file_path": file_path, "file_hash": file_hash,
                "user_id": user_id, "thread_id": thread_id, "message_id": message_id
            }) for i, chunk in enumerate(chunks)
        ]
        qdrant_client.upsert(collection_name=collection_name, points=points)
        print("Embeddings stored successfully.")
    except Exception as e:
        print(f"Error storing embeddings: {e}")
```



* This function stores embeddings for the PDF chunks in the Qdrant collection.

* Each chunk of text is processed to generate an embedding, and then a PointStruct (which consists of the vector and some metadata) is created.
* The metadata includes the original file path, file hash, and information about the user and thread, helping in filtering later when searching for relevant context.
* upsert is used to either update or insert embeddings into Qdrant.





# **Retrieving Relevant Context from Qdrant**


```python
def simple_rag_retrieve(query, top_k, user_id, thread_id, collection_name):
    try:
        query_embedding = create_embedding(query, model_embed)
        search_result = qdrant_client.search(
            collection_name=collection_name,
            query_vector=query_embedding,
            limit=top_k,
            query_filter={"must": [{"key": "user_id", "match": {"value": user_id}}]}
        )
        return "\n".join([hit.payload['chunk_text'] for hit in search_result])
    except Exception as e:
        print(f"Error retrieving context: {e}")
        return None
```

* **Retrieving Context:** When the user asks a question, this function retrieves relevant context by searching through the embeddings stored in Qdrant.

* **Query Embedding**: First, the user’s query is converted into an embedding.

* **Search:** The Qdrant database is searched for similar embeddings using the query vector, with a filter applied to ensure that only results belonging to the same user_id and thread_id are returned.

* The function returns the matching text chunks, combined into a single string.





# **Generating a Response Using Retrieved Context**


```python
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
    response.raise_for_status()
    return response.json()["choices"][0]["message"]["content"]
```

* This function sends a POST request to the Nugen API to generate a response based on the retrieved context and the user query.

* **messages:** The request includes the context retrieved from Qdrant and the user’s query. The assistant uses these messages to generate a relevant response

* The Nugen API processes this request and returns a completion (answer) that is sent back to the user.






```python
def embed_pdf(file_path, user_id, thread_id, message_id, collection_name):
    url = "https://api.nugen.in/inference/embeddings"
    with open(file_path, 'rb') as f:
        payload = {
            "input": f.read().decode(),  # Assuming the file is readable as text
            "model": "nugen-flash-embed",
            "dimensions": 123  # Set to your required dimensions
        }
    headers = {
        "Authorization": "Bearer <token>",  # Replace <token> with your actual API token
        "Content-Type": "application/json"
    }

    response = requests.post(url, json=payload, headers=headers)
    if response.status_code == 200:
        data = response.json()
        file_hash = data['file_hash']  # Adjust based on your API response structure
        file_chunks_count = data['chunks_count']  # Adjust based on your API response structure
        return True, file_hash, file_chunks_count
    else:
        return False, None, None
```


```python
def simple_rag_generate(user_query, query_context):
    url = "https://api.nugen.in/inference/completions"
    payload = {
        "max_tokens": 400,
        "model": "nugen-flash-instruct",
        "prompt": user_query + " " + (query_context or ""),
        "temperature": 1
    }
    headers = {
        "Authorization": "Bearer <token>",
        "Content-Type": "application/json"
    }

    response = requests.post(url, json=payload, headers=headers)
    if response.status_code == 200:
        data = response.json()
        return data['completion']  # Adjust based on API response structure
    else:
        return "Error generating response."
```

# **Chainlit Integration**
Chainlit is used to handle messages and file uploads.


```python
@cl.on_message
async def main(message: cl.Message):
    uploaded_files = [file for file in message.elements]

    if uploaded_files:
        for file in uploaded_files:
            embed_ready, file_hash, file_chunks_count = embed_pdf(file.path, user_id, thread_id, message_id, collection_name)
            if embed_ready:
                await cl.Message(content=f"The uploaded file '{file.name}' has been embedded.").send()
            else:
                await cl.Message(content=f"Failed to embed '{file.name}'.").send()

    user_query = message.content
    query_context = simple_rag_retrieve(user_query, top_k, user_id, thread_id, message_id, collection_name)
    answer = simple_rag_generate(user_query, query_context)

    await cl.Message(content=f"Response: {answer}").send()
```



---

By following this structure, the model enables users to upload PDFs, extract meaningful information from them, and ask questions that are answered based on the embedded content in the document. All of this is powered by Nugen’s APIs and the Qdrant vector database for high-quality search and retrieval.

