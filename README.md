# reservas-plan

## Reglas de negocio: estados de una reserva

```
pendiente ──► confirmada ──► cancelada
    │                            ▲
    └────────────────────────────┘
```

| Estado actual | Puede pasar a          | Motivo                                              |
|---------------|------------------------|-----------------------------------------------------|
| pendiente     | confirmada, cancelada  | Estado inicial de toda reserva nueva                |
| confirmada    | cancelada              | El cliente puede cancelar tras confirmar            |
| cancelada     | —                      | Estado final: no se reactiva, se crea una nueva     |

Cambiar al mismo estado no está permitido (la API responde **409 Conflict**).