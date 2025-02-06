import ollama

# Define the model name
model = "gemma2mod"  # Replace with the correct model name

prompt = ""  # Initialize prompt

while prompt.lower() != "exit":
    prompt = input("Enter your prompt: ")  # Get user input

    if prompt.lower() == "exit":  # Check exit condition
        break

    # Generate response using Ollama
    response = ollama.generate(model=model, prompt=prompt)

    # Print the response
    print("Response from Ollama:")
    print(response.response)  # Access response text properly

exit()