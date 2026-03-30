#!/bin/bash
# =============================================================
# linear_get_tasks.sh
# Consulta tareas activas en Linear y las formatea para Claude Code
#
# Uso:
#   ./scripts/linear_get_tasks.sh                    → tareas "In Progress"
#   ./scripts/linear_get_tasks.sh "Todo"             → tareas pendientes
#   ./scripts/linear_get_tasks.sh "In Progress" 10  → limitar a 10 resultados
#
# Variables de entorno requeridas:
#   LINEAR_API_KEY=lin_api_XXXXXXXXXXXXXXXX
# =============================================================

set -e

LINEAR_API_KEY="${LINEAR_API_KEY:-}"
STATUS_FILTER="${1:-In Progress}"
LIMIT="${2:-20}"

if [ -z "$LINEAR_API_KEY" ]; then
  echo "❌ Error: Variable LINEAR_API_KEY no definida"
  echo "   Ejecutar: export LINEAR_API_KEY='lin_api_XXXXXXXXXXXXXXXX'"
  exit 1
fi

echo "🔍 Consultando Linear — Estado: \"$STATUS_FILTER\" (máx $LIMIT)"
echo ""

RESPONSE=$(curl -s \
  -X POST \
  -H "Authorization: $LINEAR_API_KEY" \
  -H "Content-Type: application/json" \
  -d "{
    \"query\": \"query { issues(filter: { state: { name: { eq: \\\"$STATUS_FILTER\\\" } } }, first: $LIMIT, orderBy: updatedAt) { nodes { id identifier title description priority estimate state { name } project { name } labels { nodes { name } } assignee { name } url updatedAt } } }\"
  }" \
  https://api.linear.app/graphql)

# Verificar si hubo error en la respuesta
if echo "$RESPONSE" | python3 -c "import sys,json; d=json.load(sys.stdin); exit(0 if 'data' in d else 1)" 2>/dev/null; then
  echo "$RESPONSE" | python3 -c "
import sys, json
from datetime import datetime

data = json.load(sys.stdin)
issues = data.get('data', {}).get('issues', {}).get('nodes', [])

priority_labels = {0: 'Sin prioridad', 1: '🔴 Urgente', 2: '🟠 Alta', 3: '🟡 Media', 4: '🟢 Baja'}

if not issues:
    print('  No hay tareas con ese estado.')
    sys.exit(0)

print(f'📋 {len(issues)} tarea(s) encontrada(s)\n')
print('=' * 65)

for i in issues:
    labels = [l['name'] for l in i.get('labels', {}).get('nodes', [])]
    priority = priority_labels.get(i.get('priority', 0), 'Sin prioridad')
    project = i.get('project', {}).get('name', 'Sin proyecto') if i.get('project') else 'Sin proyecto'
    assignee = i.get('assignee', {}).get('name', 'Sin asignar') if i.get('assignee') else 'Sin asignar'
    estimate = i.get('estimate') or 'N/A'
    
    print(f\"ID:          {i['identifier']}\")
    print(f\"Título:      {i['title']}\")
    print(f\"Estado:      {i['state']['name']}\")
    print(f\"Prioridad:   {priority}\")
    print(f\"Story Points: {estimate} SP\")
    print(f\"Épica:       {project}\")
    print(f\"Asignado:    {assignee}\")
    if labels:
        print(f\"Labels:      {', '.join(labels)}\")
    print(f\"URL:         {i['url']}\")
    if i.get('description'):
        desc = i['description'][:400].strip()
        if len(i['description']) > 400:
            desc += '...'
        print(f\"Descripción:\\n{desc}\")
    print('-' * 65)
"
else
  echo "❌ Error al conectar con Linear API:"
  echo "$RESPONSE" | python3 -m json.tool 2>/dev/null || echo "$RESPONSE"
  exit 1
fi
