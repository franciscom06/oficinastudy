from typing import Dict
from fastapi import FastAPI, HTTPException
from fastapi.middleware.cors import CORSMiddleware
from pydantic import BaseModel
import ollama

#Import for rag
from sentence_transformers import SentenceTransformer, util
from openai import OpenAI
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

ollama_model = "gemma2mod3"  # Model name
conversation_history = []  # Store conversation history

if os.path.exists("vault.txt"):
    with open("vault.txt", "r", encoding="utf-8") as vault_file:
        vault_content = vault_file.readlines()

#Função para obter contexto relevante do vault
def obter_contexto_relevante(user_input, vault_embeddings, vault_content, model, top_k=2):
    if vault_embeddings.nelement() == 0:
        print("❌ Vault embeddings estão vazios!")
        return []
    input_embedding = model.encode([user_input])
    print("🔍 Embeddings calculados:", vault_embeddings)
    cos_scores = util.cos_sim(input_embedding, vault_embeddings)[0]
    top_k = min(top_k, len(cos_scores))
    top_indices = torch.topk(cos_scores, k=top_k)[1].tolist()
    contexto_relevante = [vault_content[idx].strip() for idx in top_indices]
    return contexto_relevante

def ollama_chat(user_input, vault_embeddings, vault_content, model, ollama_model, conversation_history):
    print("Entrou em ollama_chat")
    
    contexto_relevante = obter_contexto_relevante(user_input, vault_embeddings, vault_content, model)
    print("📄 Contexto relevante obtido:", contexto_relevante)
    print(contexto_relevante)
    if contexto_relevante:
        context_str = "\n".join(contexto_relevante)
        print("📚 Contexto final usado:", context_str)
        user_input_with_context = context_str + "\n\n" + user_input
    else:
        user_input_with_context = context_str + "\n\n" + user_input

    if contexto_relevante:
        print("OLA")
    conversation_history.append({"role": "user", "content": user_input_with_context})
    print("💬 Histórico atualizado:", conversation_history)

    try:
        formatted_history = "\n".join([f"{msg['role'].capitalize()}: {msg['content']}" for msg in conversation_history])
        response = ollama.generate(model=ollama_model, prompt=formatted_history)

        print("✅ Resposta do Ollama recebida")

        response_text = response["response"]
        print("📝 Texto da resposta extraído:", response_text)

        conversation_history.append({"role": "assistant", "content": response_text})
        return response_text

    except Exception as e:
        print("❌ Erro dentro da função ollama_chat:", str(e))
        raise



# Load the model and vault content
model = SentenceTransformer("all-MiniLM-L6-v2")
vault_content = []
if os.path.exists("vault.txt"):
    with open("vault.txt", "r", encoding='utf-8') as vault_file:
        vault_content = vault_file.readlines()
vault_embeddings = model.encode(vault_content) if vault_content else []

vault_embeddings_tensor = torch.tensor(vault_embeddings) 
conversation_history = []

# Define request body model
class UserInput(BaseModel):
    input: str
    estado: str


@app.post("/generate")
async def generate_response(user_input: UserInput) -> Dict[str, str]:
    print("Recebido:", user_input.estado)
    try:
        if not user_input.input:
            raise HTTPException(status_code=400, detail="No input provided")

        if user_input.estado == "Ativo":
            print("pilinha torta")
            response = ollama_chat(user_input.input, vault_embeddings_tensor, vault_content, model, ollama_model, conversation_history)
            print("pilinha direita")
            return {"response": response}

        else:
            conversation_history.append({"role": "user", "content": user_input.input})
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
