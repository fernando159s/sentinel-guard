#!/usr/bin/env python3
"""
create_cycle.py
Crea un Cycle (Sprint) en Linear para el team SentinelForms.

Uso:
    export LINEAR_API_KEY="lin_api_XXXXXXXXXXXXXXXX"
    python3 scripts/create_cycle.py

Por defecto crea un sprint de 2 semanas desde hoy.
"""

import os
import sys
import json
import requests
from datetime import datetime, timedelta

# ─────────────────────────────────────────────
# Configuración
# ─────────────────────────────────────────────
API_KEY = os.environ.get("LINEAR_API_KEY", "")
TEAM_ID = "72975163-4690-44a6-80a0-bbc09792f496"  # SentinelForms
API_URL = "https://api.linear.app/graphql"

HEADERS = {
    "Authorization": API_KEY,
    "Content-Type": "application/json",
}


def graphql(query, variables=None):
    payload = {"query": query}
    if variables:
        payload["variables"] = variables
    resp = requests.post(API_URL, headers=HEADERS, json=payload)
    data = resp.json()
    if "errors" in data:
        print(f"ERROR: {json.dumps(data['errors'], indent=2)}")
        sys.exit(1)
    return data["data"]


def get_existing_cycles():
    query = """
    query($teamId: String!) {
        team(id: $teamId) {
            cycles {
                nodes {
                    id
                    name
                    number
                    startsAt
                    endsAt
                }
            }
        }
    }
    """
    data = graphql(query, {"teamId": TEAM_ID})
    return data["team"]["cycles"]["nodes"]


def create_cycle(name, starts_at, ends_at):
    query = """
    mutation($input: CycleCreateInput!) {
        cycleCreate(input: $input) {
            success
            cycle {
                id
                name
                number
                startsAt
                endsAt
            }
        }
    }
    """
    variables = {
        "input": {
            "teamId": TEAM_ID,
            "name": name,
            "startsAt": starts_at,
            "endsAt": ends_at,
        }
    }
    data = graphql(query, variables)
    return data["cycleCreate"]


def main():
    if not API_KEY:
        print("ERROR: Falta LINEAR_API_KEY en las variables de entorno.")
        print('  export LINEAR_API_KEY="lin_api_XXXXXXXXXXXXXXXX"')
        sys.exit(1)

    # Verificar cycles existentes
    existing = get_existing_cycles()
    if existing:
        print(f"Ya existen {len(existing)} cycle(s):")
        for c in existing:
            print(f"  - #{c['number']} {c.get('name', '')} ({c['startsAt'][:10]} -> {c['endsAt'][:10]})")
        print()

    # Calcular fechas: sprint de 2 semanas desde hoy (lunes a domingo)
    today = datetime.now()
    # Buscar el lunes mas cercano (hoy si es lunes, sino el lunes anterior)
    days_since_monday = today.weekday()  # 0=lunes
    start = today - timedelta(days=days_since_monday)
    end = start + timedelta(days=13)  # 2 semanas (14 dias, termina en domingo)

    start_str = start.strftime("%Y-%m-%d")
    end_str = end.strftime("%Y-%m-%d")

    # Determinar numero de sprint
    sprint_num = len(existing) + 1
    name = f"Sprint {sprint_num}"

    print(f"Creando cycle: {name}")
    print(f"  Inicio: {start_str}")
    print(f"  Fin:    {end_str}")
    print()

    result = create_cycle(name, start_str, end_str)

    if result["success"]:
        cycle = result["cycle"]
        print(f"Cycle creado exitosamente!")
        print(f"  ID:     {cycle['id']}")
        print(f"  Nombre: {cycle.get('name', '')} (#{cycle['number']})")
        print(f"  Inicio: {cycle['startsAt'][:10]}")
        print(f"  Fin:    {cycle['endsAt'][:10]}")
    else:
        print("ERROR: No se pudo crear el cycle.")
        sys.exit(1)


if __name__ == "__main__":
    main()
