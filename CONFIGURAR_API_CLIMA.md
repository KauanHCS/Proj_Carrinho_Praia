# Configurar API de Clima no Dashboard

O Dashboard do sistema possui integração com a **OpenWeatherMap API** para exibir informações climáticas de Praia Grande/SP em tempo real.

## Passo a Passo para Configuração

### 1. Criar Conta Gratuita no OpenWeatherMap

1. Acesse: https://openweathermap.org/
2. Clique em **"Sign In"** no topo da página
3. Clique em **"Create an Account"**
4. Preencha os dados:
   - Username
   - Email
   - Password
   - Aceite os termos
5. Clique em **"Create Account"**
6. Verifique seu email e confirme a conta

### 2. Obter a API Key

1. Faça login no OpenWeatherMap
2. No menu superior, clique em **"API keys"** (ou acesse: https://home.openweathermap.org/api_keys)
3. Você verá uma chave padrão já criada (ou pode criar uma nova)
4. Copie a **API Key** (exemplo: `a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6`)

⚠️ **IMPORTANTE**: A API key pode levar até **2 horas** para ser ativada após a criação da conta.

### 3. Configurar no Sistema

Abra o arquivo: `public/assets/js/dashboard.js`

Localize a linha 449 (aproximadamente):

```javascript
const apiKey = 'SUA_API_KEY_AQUI'; // Substitua pela sua chave
```

Substitua `'SUA_API_KEY_AQUI'` pela sua chave real:

```javascript
const apiKey = 'a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6'; // Sua chave real
```

Salve o arquivo.

### 4. Testar

1. Abra o sistema no navegador
2. Faça login
3. O Dashboard será carregado automaticamente
4. Aguarde alguns segundos
5. O card de clima deve exibir:
   - Temperatura atual em °C
   - Descrição do clima (ex: "Ensolarado", "Nublado")
   - Ícone correspondente ao clima

## Plano Gratuito - Limites

O plano **gratuito** do OpenWeatherMap inclui:

- ✅ **60 chamadas por minuto**
- ✅ **1.000.000 chamadas por mês**
- ✅ **Dados climáticos atuais**
- ✅ **Sem necessidade de cartão de crédito**

Para o nosso sistema, que atualiza o clima a cada **30 segundos** (120 chamadas/hora), isso é mais que suficiente!

## Solução de Problemas

### Clima não carrega (mostra "--°C")

**Possíveis causas:**

1. **API Key não configurada**
   - Verifique se você substituiu `'SUA_API_KEY_AQUI'` pela sua chave real
   
2. **API Key ainda não ativada**
   - Aguarde até 2 horas após criar a conta
   
3. **Erro de rede**
   - Verifique se há conexão com a internet
   - Abra o Console do navegador (F12) e veja se há erros

### Console mostra erro 401 (Unauthorized)

- Sua API Key está inválida ou ainda não foi ativada
- Aguarde ou gere uma nova API Key

### Console mostra erro de CORS

- Isso não deve acontecer pois estamos fazendo requisição direta
- Verifique se não há firewall bloqueando

## Informações Técnicas

**Endpoint usado:**
```
https://api.openweathermap.org/data/2.5/weather
```

**Parâmetros:**
- `q`: Praia Grande,SP,BR (cidade, estado, país)
- `appid`: Sua API Key
- `units`: metric (temperatura em Celsius)
- `lang`: pt_br (descrições em português)

**Localização:**
- Cidade: **Praia Grande**
- Estado: **SP (São Paulo)**
- País: **BR (Brasil)**

**Atualização:**
- O clima é consultado quando o Dashboard carrega
- O botão "Atualizar" também recarrega o clima
- Atualização automática a cada **30 segundos** junto com as métricas

## Exemplo de Resposta da API

```json
{
  "weather": [
    {
      "main": "Clear",
      "description": "céu limpo"
    }
  ],
  "main": {
    "temp": 28.5
  }
}
```

**Ícones possíveis:**
- ☀️ `Clear` - Céu limpo
- ⛅ `Clouds` - Nublado
- 🌧️ `Rain` - Chuva
- 🌦️ `Drizzle` - Garoa
- ⛈️ `Thunderstorm` - Tempestade
- 🌫️ `Mist/Fog` - Neblina

## Recursos Adicionais

- Documentação oficial: https://openweathermap.org/current
- Geração de API Keys: https://home.openweathermap.org/api_keys
- Status da API: https://status.openweathermap.org/

---

**Desenvolvido para**: Sistema de Gestão de Carrinho de Praia  
**Última atualização**: Janeiro 2025
