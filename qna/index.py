from dotenv import load_dotenv
from langchain.chains import RetrievalQA
from langchain.chat_models import ChatOpenAI
from langchain.embeddings import OpenAIEmbeddings
from document_worker import DocumentWorker
from lib import askDb

load_dotenv()
embeddings = OpenAIEmbeddings()
gpt4 = 'gpt-4-1106-preview'
gpt3 = 'gpt-3.5-turbo-16k'

input_dir = 'docs'
fileLoadsPattern = '*.docx'
out_dir = 'db/snip'

documentWorker = DocumentWorker(embeddings=embeddings)
docsearch = documentWorker.process_docs(input_dir=input_dir, out_dir=out_dir, pattern=fileLoadsPattern)
# retriever = docsearch.as_retriever()


# qa = RetrievalQA.from_chain_type(
#     llm=ChatOpenAI(temperature=1, max_tokens=14000, model=gpt3),
#     chain_type="stuff",
#     retriever=docsearch.as_retriever()
# )


# query()
askDb(docsearch)