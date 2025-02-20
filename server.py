from typing import Dict
from fastapi import FastAPI, HTTPException
from fastapi.middleware.cors import CORSMiddleware
from pydantic import BaseModel
import ollama

app = FastAPI()

# Enable CORS
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],  # Adjust this for security in production
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

model = "gemma2mod3"  # Model name
conversation_history = []  # Store conversation history

# Define request body model
class UserInput(BaseModel):
    input: str

@app.post("/generate")
async def generate_response(user_input: UserInput) -> Dict[str, str]:
    try:
        global conversation_history

        if not user_input.input:
            raise HTTPException(status_code=400, detail="No input provided")

        # Add user message to history
        conversation_history.append({"role": "user", "content": user_input.input})

        # Format conversation history
        formatted_history = "\n".join(
            [f"{msg['role'].capitalize()}: {msg['content']}" for msg in conversation_history]
        )

        # Generate response
        response = ollama.generate(model=model, prompt=formatted_history)
        assistant_response = response.get('response', "")

        # Add assistant response to history
        conversation_history.append({"role": "assistant", "content": assistant_response})

        return {"response": assistant_response}

    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))

if __name__ == "__main__":
    import uvicorn
    uvicorn.run(app, host="0.0.0.0", port=5000)
