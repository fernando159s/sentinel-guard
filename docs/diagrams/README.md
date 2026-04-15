# Diagramas PlantUML — SecuriForm

Diagramas de arquitectura, flujos y entidades del proyecto.

## Estructura

```
diagrams/
├── architecture/      — Arquitectura del sistema y componentes
├── data/              — Modelo de datos (ER)
├── flows/             — Diagramas de secuencia (flujos clave)
├── roles/             — Activity diagrams por rol
└── use-cases/         — Casos de uso
```

## Como renderizar

### VS Code (recomendado)

1. Instalar extension: `jebbs.plantuml`
2. Abrir cualquier `.puml`
3. `Alt+D` para preview en vivo

### Docker (batch)

```bash
make diagrams
```

Esto genera PNGs junto a cada `.puml` usando la imagen `plantuml/plantuml`.

### Online

Copiar el contenido de un `.puml` en [plantuml.com/plantuml](https://www.plantuml.com/plantuml/uml/).

### CLI (requiere Java)

```bash
java -jar plantuml.jar docs/diagrams/**/*.puml
```
