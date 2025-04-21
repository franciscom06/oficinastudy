from typing import Dict
from fastapi import FastAPI, HTTPException, Request
from fastapi.middleware.cors import CORSMiddleware
from pydantic import BaseModel
import ollama

#Import for rag
from sentence_transformers import SentenceTransformer, util
import torch
import os

app = FastAPI()

# Enable CORS
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],  # Adjust this for security in production
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

ollama_model = "gemma3:12b"  # Model name
conversation_history = []  # Store conversation history
# Load the model and vault content
model = SentenceTransformer("all-MiniLM-L6-v2")
vault_content = []
if os.path.exists("vault.txt"):
    with open("vault.txt", "r", encoding='utf-8') as vault_file:
        vault_content = vault_file.readlines()
vault_embeddings = model.encode(vault_content) if vault_content else []

vault_embeddings_tensor = torch.tensor(vault_embeddings) 
conversation_history = []

if os.path.exists("vault.txt"):
    with open("vault.txt", "r", encoding="utf-8") as vault_file:
        vault_content = vault_file.readlines()

#Função para obter contexto relevante do vault
def obter_contexto_relevante(user_input, vault_embeddings, vault_content, model, top_k=2):
    #if vault_embeddings.nelement() == 0:
     #   print("❌ Vault embeddings estão vazios!")
      #  return []
    input_embedding = model.encode([user_input])
    print("🔍 Embeddings calculados:", vault_embeddings)
    cos_scores = util.cos_sim(input_embedding, vault_embeddings)[0]
    top_k = min(top_k, len(cos_scores))
    top_indices = torch.topk(cos_scores, k=top_k)[1].tolist()
    contexto_relevante = [vault_content[idx].strip() for idx in top_indices]
    return contexto_relevante


def ollama_chat(user_input, vault_embeddings, vault_content, model, ollama_model, conversation_history):
    contexto_relevante = obter_contexto_relevante(user_input, vault_embeddings, vault_content, model)
    if contexto_relevante:
        context_str = "\n".join(contexto_relevante)
        user_input_with_context = context_str + "\n\n" + user_input
    else:
        user_input_with_context = user_input

    try:
        conversation_history.append({"role": "user", "content": user_input_with_context})
        formatted_history = "\n".join([f"{msg['role'].capitalize()}: {msg['content']}" for msg in conversation_history])
        response = ollama.generate(model=ollama_model, prompt=formatted_history)


        response_text = response["response"]

        conversation_history.append({"role": "assistant", "content": response_text})
        return response_text

    except Exception as e:
        raise

class ChatRequest(BaseModel):
    user_input: str

@app.post("/ollama_chat")
async def chamar_ollama(requests: ChatRequest):
    resposta = ollama_chat(requests.user_input, vault_embeddings, vault_content, model, ollama_model, conversation_history)
    return {"resposta": resposta}


# Define request body model
class UserInput(BaseModel):
    input: str



estado_global = {"ativo": False}

@app.get("/Get_rag")
async def receive_rag():
    global estado_global
    # Inverte o estado atual, independentemente do valor recebido
    estado_global["ativo"] = not estado_global["ativo"]
    return {"verif": estado_global["ativo"]}#Retorna o estado atualizado


@app.get("/get_verif_rag") #Envia a informação do rag para o frontend e para o generate
async def verif_rag_send():
    global estado_global
    return {"verif": estado_global["ativo"]}


@app.post("/generate")
async def generate_response(user_input: ChatRequest) -> Dict[str, str]:
    try:
        if not user_input.user_input:
            raise HTTPException(status_code=400, detail="No input provided")

        conversation_history.append({"role": "user", "content": user_input.user_input})
        formatted_history = "\n".join(
            [f"{msg['role'].capitalize()}: {msg['content']}" for msg in conversation_history]
        )   
        response = ollama.generate(model=ollama_model, prompt=formatted_history)
        assistant_response = response.get('response', "")

        conversation_history.append({"role": "assistant", "content": assistant_response})

        return {"response": assistant_response}

    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))

if __name__ == "__main__":
    import uvicorn
    uvicorn.run(app, host="0.0.0.0", port=5000)
