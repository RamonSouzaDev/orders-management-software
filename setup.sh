#!/bin/bash

#===============================================================================
# Script de Setup - Sistema de Pedidos
# Orders Management System
#===============================================================================

set -e

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Funções de log
log_info() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

log_success() {
    echo -e "${GREEN}[OK]${NC} $1"
}

log_warning() {
    echo -e "${YELLOW}[AVISO]${NC} $1"
}

log_error() {
    echo -e "${RED}[ERRO]${NC} $1"
}

# Banner
echo ""
echo -e "${GREEN}╔═══════════════════════════════════════════════════════════╗${NC}"
echo -e "${GREEN}║       Sistema de Pedidos - Setup Automático               ║${NC}"
echo -e "${GREEN}║             Orders Management System                      ║${NC}"
echo -e "${GREEN}╚═══════════════════════════════════════════════════════════╝${NC}"
echo ""

# Verificar se Docker está instalado
log_info "Verificando Docker..."
if ! command -v docker &> /dev/null; then
    log_error "Docker não encontrado. Por favor, instale o Docker primeiro."
    exit 1
fi
log_success "Docker encontrado!"

# Verificar se Docker Compose está instalado
log_info "Verificando Docker Compose..."
if ! command -v docker-compose &> /dev/null && ! docker compose version &> /dev/null; then
    log_error "Docker Compose não encontrado. Por favor, instale o Docker Compose."
    exit 1
fi
log_success "Docker Compose encontrado!"

# Criar arquivo .env se não existir
log_info "Configurando arquivo .env..."
if [ ! -f .env ]; then
    if [ -f env.example ]; then
        cp env.example .env
        log_success "Arquivo .env criado a partir do env.example"
    else
        log_error "Arquivo env.example não encontrado!"
        exit 1
    fi
else
    log_warning "Arquivo .env já existe, mantendo configuração atual"
fi

# Criar diretórios necessários
log_info "Criando diretórios necessários..."
mkdir -p storage/app/public
mkdir -p storage/framework/cache/data
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/logs
mkdir -p bootstrap/cache
log_success "Diretórios criados!"

# Parar containers existentes
log_info "Parando containers existentes..."
docker-compose down 2>/dev/null || true
log_success "Containers parados!"

# Build e start dos containers
log_info "Construindo e iniciando containers Docker..."
echo ""
docker-compose up -d --build

# Aguardar MySQL iniciar
log_info "Aguardando MySQL iniciar (pode levar até 30 segundos)..."
sleep 10

# Verificar se MySQL está pronto
MAX_TRIES=30
TRIES=0
until docker-compose exec -T mysql mysqladmin ping -h"localhost" -uroot -proot --silent 2>/dev/null; do
    TRIES=$((TRIES+1))
    if [ $TRIES -gt $MAX_TRIES ]; then
        log_error "MySQL não iniciou a tempo. Verifique os logs com: docker-compose logs mysql"
        exit 1
    fi
    echo -n "."
    sleep 1
done
echo ""
log_success "MySQL está pronto!"

# Instalar dependências do Composer
log_info "Instalando dependências do Composer..."
docker-compose exec -T app composer install --no-interaction --prefer-dist --optimize-autoloader
log_success "Dependências instaladas!"

# Gerar chave da aplicação
log_info "Gerando chave da aplicação..."
docker-compose exec -T app php artisan key:generate --force
log_success "Chave gerada!"

# Limpar cache
log_info "Limpando cache..."
docker-compose exec -T app php artisan config:clear
docker-compose exec -T app php artisan cache:clear
log_success "Cache limpo!"

# Executar migrations
log_info "Executando migrations..."
docker-compose exec -T app php artisan migrate --force
log_success "Migrations executadas!"

# Definir permissões
log_info "Definindo permissões..."
docker-compose exec -T app chmod -R 775 storage bootstrap/cache
log_success "Permissões definidas!"

# Verificar status dos containers
echo ""
log_info "Status dos containers:"
docker-compose ps

# Resumo final
echo ""
echo -e "${GREEN}╔═══════════════════════════════════════════════════════════╗${NC}"
echo -e "${GREEN}║                    SETUP CONCLUÍDO!                       ║${NC}"
echo -e "${GREEN}╚═══════════════════════════════════════════════════════════╝${NC}"
echo ""
echo -e "${BLUE}Acesse:${NC}"
echo -e "  • Frontend:    ${GREEN}http://localhost:8000${NC}"
echo -e "  • API:         ${GREEN}http://localhost:8000/api${NC}"
echo -e "  • Health:      ${GREEN}http://localhost:8000/api/health${NC}"
echo ""
echo -e "${BLUE}Comandos úteis:${NC}"
echo -e "  • Ver logs:           ${YELLOW}docker-compose logs -f${NC}"
echo -e "  • Parar containers:   ${YELLOW}docker-compose down${NC}"
echo -e "  • Executar testes:    ${YELLOW}docker-compose exec app php artisan test${NC}"
echo -e "  • Acessar container:  ${YELLOW}docker-compose exec app bash${NC}"
echo ""

