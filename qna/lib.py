from langchain.vectorstores.chroma import Chroma


def askDb(docsearch: Chroma):
    while True:
        q = input("Enter your query or type 'exit' to quit: ")
        docs = docsearch.similarity_search(q)
        if q.lower() == 'exit':
            break

        print("Query: ", q)
        print("Answer: ")
        for doc in docs:
            print(doc.page_content)


def query():
    while True:
        q = input("Enter your query or type 'exit' to quit: ")
        if q.lower() == 'exit':
            break
        print("Query: ", q)
        print("Answer: ", qa.run(q))
