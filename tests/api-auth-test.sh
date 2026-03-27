#!/bin/bash

# Script di test per verificare il funzionamento dell'API Key authentication

echo "=================================================="
echo "Test API Authentication - Cartino"
echo "=================================================="
echo ""

# Configurazione
API_URL="${API_URL:-http://localhost:8000}"
API_KEY="${CARTINO_TEST_API_KEY:-ck_demo_test_key}"

echo "🔧 Configurazione:"
echo "   API URL: $API_URL"
echo "   API Key: ${API_KEY:0:20}..."
echo ""

# Colori per output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Funzione per testare endpoint
test_endpoint() {
    local method=$1
    local endpoint=$2
    local use_auth=$3
    local description=$4
    
    echo -n "Testing $method $endpoint ... "
    
    if [ "$use_auth" = "true" ]; then
        response=$(curl -s -w "\n%{http_code}" -X "$method" \
            -H "X-API-Key: $API_KEY" \
            -H "Accept: application/json" \
            "$API_URL$endpoint")
    else
        response=$(curl -s -w "\n%{http_code}" -X "$method" \
            -H "Accept: application/json" \
            "$API_URL$endpoint")
    fi
    
    http_code=$(echo "$response" | tail -n1)
    body=$(echo "$response" | sed '$d')
    
    if [ "$http_code" = "200" ] || [ "$http_code" = "201" ]; then
        echo -e "${GREEN}✓ OK${NC} ($http_code) - $description"
    elif [ "$http_code" = "401" ]; then
        echo -e "${YELLOW}⚠ UNAUTHORIZED${NC} ($http_code) - $description"
    elif [ "$http_code" = "403" ]; then
        echo -e "${YELLOW}⚠ FORBIDDEN${NC} ($http_code) - $description"
    else
        echo -e "${RED}✗ FAIL${NC} ($http_code) - $description"
        # echo "   Response: $body"
    fi
}

echo "=================================================="
echo "1. Test Endpoint Pubblici (senza auth)"
echo "=================================================="

test_endpoint "GET" "/api/products" "false" "Lista prodotti pubblici"
test_endpoint "GET" "/api/categories" "false" "Lista categorie pubbliche"
test_endpoint "GET" "/api/brands" "false" "Lista brand pubblici"
test_endpoint "GET" "/api/markets" "false" "Informazioni mercati"
test_endpoint "GET" "/api/data/statuses" "false" "Statuses pubblici"

echo ""
echo "=================================================="
echo "2. Test Endpoint Protetti (SENZA API Key)"
echo "=================================================="
echo "   Questi dovrebbero restituire 401 Unauthorized"
echo ""

test_endpoint "GET" "/api/permissions" "false" "Permissions (dovrebbe fallire)"
test_endpoint "GET" "/api/users" "false" "Users (dovrebbe fallire)"
test_endpoint "GET" "/api/api-keys" "false" "API Keys (dovrebbe fallire)"
test_endpoint "GET" "/api/reports/dashboard" "false" "Dashboard (dovrebbe fallire)"

echo ""
echo "=================================================="
echo "3. Test Endpoint Protetti (CON API Key)"
echo "=================================================="
echo "   Questi dovrebbero funzionare con l'API key"
echo ""

test_endpoint "GET" "/api/permissions" "true" "Permissions con API key"
test_endpoint "GET" "/api/users" "true" "Users con API key"
test_endpoint "GET" "/api/api-keys" "true" "API Keys con API key"
test_endpoint "GET" "/api/reports/dashboard" "true" "Dashboard con API key"

echo ""
echo "=================================================="
echo "4. Test API Key Management"
echo "=================================================="

test_endpoint "GET" "/api/api-keys" "true" "Lista API keys"

echo ""
echo "=================================================="
echo "5. Test con Query Parameter"
echo "=================================================="

echo -n "Testing GET /api/permissions?api_key=... "
response=$(curl -s -w "\n%{http_code}" \
    "$API_URL/api/permissions?api_key=$API_KEY")

http_code=$(echo "$response" | tail -n1)

if [ "$http_code" = "200" ]; then
    echo -e "${GREEN}✓ OK${NC} ($http_code) - API Key via query parameter funziona"
else
    echo -e "${RED}✗ FAIL${NC} ($http_code) - API Key via query parameter non funziona"
fi

echo ""
echo "=================================================="
echo "Test completati!"
echo "=================================================="
echo ""
echo "Note:"
echo "- Gli endpoint pubblici devono essere accessibili senza autenticazione"
echo "- Gli endpoint admin senza API key devono restituire 401"
echo "- Gli endpoint admin con API key valida devono funzionare"
echo ""
echo "Se il server non è in esecuzione, avvialo con:"
echo "  php artisan serve"
echo ""
