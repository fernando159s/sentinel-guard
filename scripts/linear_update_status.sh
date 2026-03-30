#!/bin/bash
# =============================================================
# linear_update_status.sh
# Actualiza el estado de un issue en Linear desde la terminal
#
# Uso:
#   ./scripts/linear_update_status.sh SEC-42 "Done"
#   ./scripts/linear_update_status.sh SEC-15 "In Progress"
#   ./scripts/linear_update_status.sh SEC-08 "In Review"
#
# Estados disponibles (según configuración del workspace):
#   Backlog | Todo | In Progress | In Review | Done
#
# Variables de entorno requeridas:
#   LINEAR_API_KEY=lin_api_XXXXXXXXXXXXXXXX
# =============================================================

set -e

LINEAR_API_KEY="${LINEAR_API_KEY:-}"
ISSUE_IDENTIFIER="${1:-}"
NEW_STATE="${2:-}"

if [ -z "$LINEAR_API_KEY" ]; then
  echo "❌ Error: Variable LINEAR_API_KEY no definida"
  echo "   Ejecutar: export LINEAR_API_KEY='lin_api_XXXXXXXXXXXXXXXX'"
  exit 1
fi

if [ -z "$ISSUE_IDENTIFIER" ] || [ -z "$NEW_STATE" ]; then
  echo "❌ Uso: $0 ISSUE_IDENTIFIER NUEVO_ESTADO"
  echo "   Ejemplo: $0 SEC-42 Done"
  echo ""
  echo "   Estados válidos: Backlog | Todo | In Progress | In Review | Done"
  exit 1
fi

echo "🔄 Actualizando $ISSUE_IDENTIFIER → \"$NEW_STATE\"..."

# Obtener IDs internos del issue y del estado destino
GET_RESPONSE=$(curl -s \
  -X POST \
  -H "Authorization: $LINEAR_API_KEY" \
  -H "Content-Type: application/json" \
  -d "{
    \"query\": \"query {
      issues(filter: { identifier: { eq: \\\"$ISSUE_IDENTIFIER\\\" } }) {
        nodes { id identifier title state { id name } }
      }
      workflowStates(filter: { name: { eq: \\\"$NEW_STATE\\\" } }) {
        nodes { id name }
      }
    }\"
  }" \
  https://api.linear.app/graphql)

# Extraer IDs y validar
RESULT=$(echo "$GET_RESPONSE" | python3 -c "
import sys, json

data = json.load(sys.stdin)

issues = data.get('data', {}).get('issues', {}).get('nodes', [])
states = data.get('data', {}).get('workflowStates', {}).get('nodes', [])

if not issues:
    print('ERROR:issue_not_found')
    sys.exit(0)

if not states:
    print('ERROR:state_not_found')
    sys.exit(0)

issue = issues[0]
state = states[0]
current_state = issue['state']['name']

print(f\"{issue['id']}|{state['id']}|{issue['title'][:60]}|{current_state}\")
")

if [[ "$RESULT" == ERROR:issue_not_found ]]; then
  echo "❌ Issue '$ISSUE_IDENTIFIER' no encontrado en Linear"
  exit 1
fi

if [[ "$RESULT" == ERROR:state_not_found ]]; then
  echo "❌ Estado '$NEW_STATE' no encontrado en el workspace"
  echo "   Estados disponibles: Backlog | Todo | In Progress | In Review | Done"
  exit 1
fi

ISSUE_ID=$(echo "$RESULT" | cut -d'|' -f1)
STATE_ID=$(echo "$RESULT" | cut -d'|' -f2)
ISSUE_TITLE=$(echo "$RESULT" | cut -d'|' -f3)
CURRENT_STATE=$(echo "$RESULT" | cut -d'|' -f4)

echo "   Título: $ISSUE_TITLE"
echo "   Estado actual: $CURRENT_STATE"

# Ejecutar la actualización
UPDATE_RESPONSE=$(curl -s \
  -X POST \
  -H "Authorization: $LINEAR_API_KEY" \
  -H "Content-Type: application/json" \
  -d "{
    \"query\": \"mutation {
      issueUpdate(id: \\\"$ISSUE_ID\\\", input: { stateId: \\\"$STATE_ID\\\" }) {
        success
        issue { identifier state { name } }
      }
    }\"
  }" \
  https://api.linear.app/graphql)

echo "$UPDATE_RESPONSE" | python3 -c "
import sys, json

data = json.load(sys.stdin)
result = data.get('data', {}).get('issueUpdate', {})

if result.get('success'):
    issue = result['issue']
    print(f\"✅ {issue['identifier']} actualizado a: {issue['state']['name']}\")
else:
    errors = data.get('errors', [])
    print('❌ Error al actualizar:')
    for e in errors:
        print(f\"   {e.get('message', 'Error desconocido')}\")
    sys.exit(1)
"
