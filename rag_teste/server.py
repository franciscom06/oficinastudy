from typing import Dict
from fastapi import FastAPI, HTTPException
from fastapi.middleware.cors import CORSMiddleware
from pydantic import BaseModel
import ollama
import torch
from sentence_transformers import SentenceTransformer, util
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

# Modelo do Ollama
OLLAMA_MODEL = "gemma2mod3"  # Ou o modelo que você está usando
conversation_history = []  # Armazenar histórico de conversa

# Variável para controlar o estado do botão de pesquisa
search_vault_enabled = False

# Classe para entrada do usuário
class UserInput(BaseModel):
    input: str

# Carregar conteúdo do "vault.txt"
vault_content = []
if os.path.exists("vault.txt"):
    with open("vault.txt", "r", encoding='utf-8') as vault_file:
        vault_content = vault_file.readlines()

# Carregar o modelo de embeddings
embedding_model = SentenceTransformer("all-MiniLM-L6-v2")
vault_embeddings = embedding_model.encode(vault_content) if vault_content else []
vault_embeddings_tensor = torch.tensor(vault_embeddings) if len(vault_embeddings) > 0 else torch.empty(0)

# Função para obter contexto relevante do vault
def get_relevant_context(user_input: str, top_k=2):
    if vault_embeddings_tensor.nelement() == 0:  # Se não houver embeddings
        return []
    
    input_embedding = embedding_model.encode([user_input])
    cos_scores = util.cos_sim(torch.tensor(input_embedding), vault_embeddings_tensor)[0]
    
    top_k = min(top_k, len(cos_scores))
    top_indices = torch.topk(cos_scores, k=top_k)[1].tolist()
    
    return [vault_content[idx].strip() for idx in top_indices]

# Função para gerar resposta
def ollama_chat(user_input: str):
    global search_vault_enabled
    
    relevant_context = []
    if search_vault_enabled:
        relevant_context = get_relevant_context(user_input)
    
    if relevant_context:
        context_str = "\n".join(relevant_context)
        user_input_with_context = context_str + "\n\n" + user_input
    else:
        user_input_with_context = user_input
    
    conversation_history.append({"role": "user", "content": user_input_with_context})
    
    # Formata o histórico
    formatted_history = "\n".join([f"{msg['role'].capitalize()}: {msg['content']}" for msg in conversation_history])

    # Gera resposta com Ollama
    response = ollama.generate(model=OLLAMA_MODEL, prompt=formatted_history)
    assistant_response = response.get('response', "")

    # Adiciona a resposta ao histórico
    conversation_history.append({"role": "assistant", "content": assistant_response})

    return assistant_response

@app.post("/generate")
async def generate_response(user_input: UserInput) -> Dict[str, str]:
    try:
        if not user_input.input:
            raise HTTPException(status_code=400, detail="No input provided")

        response_text = ollama_chat(user_input.input)
        return {"response": response_text}

    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))

# Rota para alternar o estado de pesquisa no vault
@app.post("/toggle-vault")
async def toggle_vault() -> Dict[str, str]:
    global search_vault_enabled
    search_vault_enabled = not search_vault_enabled
    return {"status": "Ativado" if search_vault_enabled else "Desativado"}

if __name__ == "__main__":
    import uvicorn
    uvicorn.run(app, host="0.0.0.0", port=5000)
