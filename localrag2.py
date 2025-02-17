import torch
from sentence_transformers import SentenceTransformer, util
import os
from openai import OpenAI
import argparse
from deep_translator import GoogleTranslator

#from translate import Translator

# ANSI escape codes for colors
PINK = '\033[95m'
CYAN = '\033[96m'
YELLOW = '\033[93m'
NEON_GREEN = '\033[92m'
RESET_COLOR = '\033[0m'

#Translate
def traduzir_para_pt_pt(texto):
    # Traduz do português do Brasil para inglês e depois para português de Portugal
    traduzido = GoogleTranslator(source='pt', target='en').translate(texto)
    final = GoogleTranslator(source='en', target='pt').translate(traduzido)
    return final

# Function to open a file and return its contents as a string
def open_file(filepath):
    with open(filepath, 'r', encoding='utf-8') as infile:
        return infile.read()

# Function to get relevant context from the vault based on user input
def get_relevant_context(user_input, vault_embeddings, vault_content, model, top_k=2):
    if vault_embeddings.nelement() == 0:  # Check if the tensor has any elements
        return []
    # Encode the user input
    input_embedding = model.encode([user_input])
    # Compute cosine similarity between the input and vault embeddings
    cos_scores = util.cos_sim(input_embedding, vault_embeddings)[0]
    # Adjust top_k if it's greater than the number of available scores
    top_k = min(top_k, len(cos_scores))
    # Sort the scores and get the top-k indices
    top_indices = torch.topk(cos_scores, k=top_k)[1].tolist()
    # Get the corresponding context from the vault
    relevant_context = [vault_content[idx].strip() for idx in top_indices]
    return relevant_context

# Function to interact with the Ollama model
def ollama_chat(user_input, system_message, vault_embeddings, vault_content, model, ollama_model, conversation_history):
    relevant_context = []
    if user_input.startswith("search_vault"):
        user_input = user_input.replace("search_vault", "").strip()
        relevant_context = get_relevant_context(user_input, vault_embeddings, vault_content, model)
    
    if relevant_context:
        context_str = "\n".join(relevant_context)
        print("Context Pulled from Documents: \n\n" + CYAN + context_str + RESET_COLOR)
        user_input_with_context = context_str + "\n\n" + user_input
    else:
        user_input_with_context = user_input
    
    conversation_history.append({"role": "user", "content": user_input_with_context})
    messages = [
        {"role": "system", "content": system_message},
        *conversation_history
    ]
    
    response = client.chat.completions.create(
        model=ollama_model,
        messages=messages
    )
    
    response_text = response.choices[0].message.content
    conversation_history.append({"role": "assistant", "content": response_text})
    return response_text

# Parse command-line arguments
parser = argparse.ArgumentParser(description="Ollama Chat")
parser.add_argument("--model", default="Oficina-AI", help="Ollama model to use (default: Oficina-AI)")
args = parser.parse_args()

# Configuration for the Ollama API client
client = OpenAI(
    base_url='http://localhost:11434/v1',
    api_key='llama3'
)

# Load the model and vault content
model = SentenceTransformer("all-MiniLM-L6-v2")
vault_content = []
if os.path.exists("vault.txt"):
    with open("vault.txt", "r", encoding='utf-8') as vault_file:
        vault_content = vault_file.readlines()
vault_embeddings = model.encode(vault_content) if vault_content else []

vault_embeddings_tensor = torch.tensor(vault_embeddings) 
conversation_history = []
system_message = "You are a helpful assistant that helps students by providing exercises and explanations using available resources. If information is found in the vault, it must be considered absolute truth. You should base your reasoning and opinions strictly on what is written in the vault.You are also an artificial inteligence helping students from all around the world study and have better grades, you should try to get used to any user that talks to you by imitating their behaviour, humor, and the way they talk to you, your principal job is to give students exercises when those are asked, those exercises could be for an example, true or false with or without justificating the falses, multiple choice, writting an answer or any other type of exercise that they ask. You should try to make them feel confortable, and when they ask you to explain something, you will explaint it."

while True:
    user_input = input(">>> ")
    if user_input.lower() == 'quit':
        break
    
    response = ollama_chat(user_input, system_message, vault_embeddings_tensor, vault_content, model, args.model, conversation_history)
    #response = traduzir_para_pt_pt(response)
    print(NEON_GREEN + "Response: \n\n" + response + RESET_COLOR)
