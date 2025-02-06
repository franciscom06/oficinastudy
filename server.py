from flask import Flask, request, jsonify
from flask_cors import CORS
import ollama

# iniciar o flaskapp
app = Flask(__name__)

CORS(app)  # permitir cross-origin requests


#################################################################################################
# como ligar o servidor - executar no terminal - python server.py e em seguida abrir index.html #
#################################################################################################


model = "gemma2mod"  # Nome do modelo a ser usado

@app.route("/generate", methods=["POST"])
def generate_response():
    try:
        # Guarda o input na variavel data
        data = request.json

        # Guarda a data do input na variavel prompt, se nao for inserido o default é nulo
        prompt = data.get("input", "")

        # Se for nulo mostra o erro "no input provided" e identifica-o com o código de erro "400" (Bad Request)
        if not prompt:
            return jsonify({"error": "No input provided"}), 400

        # Gerar a resposta
        response = ollama.generate(model=model, prompt=prompt)

        # Returnar a resposta em json
        return jsonify({"response": response.response})

    # Mostra qualquer erro que houve durante a execução e identifica-o com o código de erro "500" (Internal Server Error).
    except Exception as e:
        return jsonify({"error": str(e)}), 500

if __name__ == "__main__":
    # Dar run em localhost:5000
    app.run(host="0.0.0.0", port=5000)