FROM python:3.10-slim

WORKDIR /app

# Remove the 'price_api/' prefix because Docker is already inside that folder
COPY requirements.txt .

RUN pip install --no-cache-dir -r requirements.txt

# Use '.' to copy everything from the current folder into the container
COPY . .

EXPOSE 8000

CMD ["uvicorn", "main:app", "--host", "0.0.0.0", "--port", "8000"]