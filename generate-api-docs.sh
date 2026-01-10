#!/bin/bash

# API Documentation Generator Script
# This script generates API documentation from OpenAPI spec

set -e

echo "🔧 Generating API Documentation..."

# Check if openapi.yaml exists
if [ ! -f "openapi.yaml" ]; then
    echo "❌ Error: openapi.yaml not found"
    exit 1
fi

# Install Redoc CLI if not installed
if ! command -v redoc-cli &> /dev/null; then
    echo "📦 Installing Redoc CLI..."
    npm install -g redoc-cli
fi

# Create docs directory
mkdir -p public/api-docs

# Generate HTML documentation
echo "📝 Generating HTML documentation..."
redoc-cli bundle openapi.yaml -o public/api-docs/index.html --title="Multilingual Chat Platform API"

# Generate Swagger UI (optional)
if command -v swagger-cli &> /dev/null; then
    echo "📝 Generating Swagger UI..."
    swagger-cli validate openapi.yaml
fi

echo "✅ API Documentation generated successfully!"
echo "📄 View at: http://localhost:8000/api-docs"
echo ""
echo "Alternative viewers:"
echo "  - Swagger Editor: https://editor.swagger.io/"
echo "  - Redocly: https://redocly.com/docs/"
echo ""
echo "To serve locally:"
echo "  npx redoc-cli serve openapi.yaml"
