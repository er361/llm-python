from dotenv import load_dotenv
from langchain.chains import RetrievalQA
from langchain.chat_models import ChatOpenAI
from langchain.embeddings import OpenAIEmbeddings
from document_worker import DocumentWorker

load_dotenv()

embeddings = OpenAIEmbeddings()
gpt4 = 'gpt-4-32k'
gpt3 = 'gpt-3.5-turbo-16k'

input_dir = './*.docx'
out_dir = 'db/models'

documentWorker = DocumentWorker(embeddings=embeddings)
docsearch = documentWorker.process_docs(input_dir=input_dir, out_dir=out_dir)
retriever = docsearch.as_retriever()

docs = retriever.get_relevant_documents("How much money did Pando raise?")
print(len(docs))

print(retriever.search_type)

qa = RetrievalQA.from_chain_type(
    llm=ChatOpenAI(temperature=1, max_tokens=14000, model=gpt3),
    chain_type="stuff",
    retriever=docsearch.as_retriever()
)


def query(q):
    print("Query: ", q)
    print("Answer: ", qa.run(q))

query('на какой высоте ставить лампочку выход? ответ бери только в из документа')

# query('Категория электроснабжения жилого дома 22 этажа')
