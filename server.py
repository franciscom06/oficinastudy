from flask import Flask, request, jsonify
from flask_cors import CORS
import ollama

app = Flask(__name__)

# comunicar backend com frontend
CORS(app)

model = "gemma2mod3"  # nome do modelo a ser usado

# armazena o histórico da conversa como um array
conversation_history = []

# definimos a rota /generate, que recebe requisições POST
@app.route("/generate", methods=["POST"])
def generate_response():
    try:
        global conversation_history

        # recolhe os dados enviados pelo utilizador como formato JSON.
        data = request.json
        user_input = data.get("input", "")

        # caso o input seja inválido
        if not user_input:
            return jsonify({"error": "No input provided"}), 400

        # adiciona a mensagem do utilizador ao histórico
        conversation_history.append({"role": "user", "content": user_input})

        # formata o histórico para enviar ao modelo 
        formatted_history = "\n".join(
            [f"{msg['role'].capitalize()}: {msg['content']}" for msg in conversation_history]
        )

        # Gera a resposta baseada no histórico completo
        response = ollama.generate(model=model, prompt=formatted_history)

        # Adiciona a resposta ao histórico
        conversation_history.append({"role": "assistant", "content": response['response']})

        # retorna a resposta gerada como json
        return jsonify({"response": response['response']})

    except Exception as e:
        return jsonify({"error": str(e)}), 500

if __name__ == "__main__":
    app.run(host="0.0.0.0", port=5000)

