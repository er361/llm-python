# document_worker.py
import chromadb
from langchain.document_loaders import DirectoryLoader
from langchain.text_splitter import RecursiveCharacterTextSplitter
from langchain.vectorstores import Chroma


def get_documents(pattern, dir_name='docs'):
    loader = DirectoryLoader(dir_name, glob=pattern, show_progress=True)
    documents = loader.load()
    print(len(documents))
    return documents


def split_documents(documents):
    text_splitter = RecursiveCharacterTextSplitter(
        chunk_size=500, chunk_overlap=50)
    texts = text_splitter.split_documents(documents)
    # for text in texts:
    # print text each in new line
    #     print(text,end='\n______________________________________________________________\n')
    return texts


def save_documents(embeddings, dir_name, texts) -> Chroma:
    client = chromadb.PersistentClient(path='./' + dir_name)
    db = Chroma.from_documents(documents=texts, embedding=embeddings, client=client, collection_name='docs')
    collection = client.get_collection(name='docs')
    print(collection.count())
    return db


def get_chroma(embeddings, dir_name) -> Chroma:
    client = chromadb.PersistentClient(path='./' + dir_name)
    return Chroma(client=client, embedding_function=embeddings, collection_name='docs')


def check_if_collection_exists(dir_name):
    client = chromadb.PersistentClient(path='./' + dir_name)
    try:
        collection = client.get_collection(name='docs')
        count = collection.count()
        if count > 0:
            return True
    except ValueError:
        print('Collection does not exist')
        return False

    return False


class DocumentWorker:
    def __init__(self, embeddings):
        self.embeddings = embeddings

    def process_docs(self, input_dir='**/*.docx', out_dir='snip') -> None | Chroma:
        if check_if_collection_exists(out_dir):
            print('Collection exists, skipping...')
            return get_chroma(self.embeddings, out_dir)

        documents = get_documents(input_dir)
        texts = split_documents(documents)

        return save_documents(self.embeddings, out_dir, texts)

    def get_retriever(self) -> Chroma:
        docsearch = Chroma(persist_directory='./snip',
                           embedding_function=self.embeddings)
        return docsearch
