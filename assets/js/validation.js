// Input validation and sanitization utilities

class Validation {
    
    // HTML sanitization to prevent XSS
    static sanitizeHtml(str) {
        if (typeof str !== 'string') return str;
        
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }
    
    // Validate email format
    static isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }
    
    // Validate phone number (Brazilian format)
    static isValidPhone(phone) {
        const phoneRegex = /^\(\d{2}\)\s\d{4,5}-\d{4}$/;
        return phoneRegex.test(phone);
    }
    
    // Validate price (positive number with up to 2 decimal places)
    static isValidPrice(price) {
        const priceRegex = /^\d+(\.\d{1,2})?$/;
        return priceRegex.test(price) && parseFloat(price) > 0;
    }
    
    // Validate quantity (positive integer)
    static isValidQuantity(quantity) {
        return Number.isInteger(Number(quantity)) && Number(quantity) > 0;
    }
    
    // Validate product name (not empty, reasonable length)
    static isValidProductName(name) {
        return typeof name === 'string' && 
               name.trim().length >= 2 && 
               name.trim().length <= 100;
    }
    
    // Password validation (at least 6 characters)
    static isValidPassword(password) {
        return typeof password === 'string' && password.length >= 6;
    }
    
    // Validate form data for product
    static validateProduct(data) {
        const errors = [];
        
        if (!this.isValidProductName(data.nome)) {
            errors.push('Nome do produto deve ter entre 2 e 100 caracteres');
        }
        
        if (!data.categoria || !['bebida', 'comida', 'outros'].includes(data.categoria)) {
            errors.push('Categoria inválida');
        }
        
        if (!this.isValidPrice(data.preco)) {
            errors.push('Preço deve ser um valor positivo');
        }
        
        if (!this.isValidQuantity(data.quantidade)) {
            errors.push('Quantidade deve ser um número inteiro positivo');
        }
        
        if (!this.isValidQuantity(data.limite_minimo)) {
            errors.push('Limite mínimo deve ser um número inteiro positivo');
        }
        
        // Validate date if provided
        if (data.validade && data.validade !== '') {
            const date = new Date(data.validade);
            const today = new Date();
            if (isNaN(date.getTime()) || date < today) {
                errors.push('Data de validade deve ser futura');
            }
        }
        
        return {
            isValid: errors.length === 0,
            errors: errors
        };
    }
    
    // Validate registration form data
    static validateRegistration(data) {
        const errors = [];
        
        if (!data.nome || data.nome.trim().length < 2) {
            errors.push('Nome deve ter pelo menos 2 caracteres');
        }
        
        if (!data.sobrenome || data.sobrenome.trim().length < 2) {
            errors.push('Sobrenome deve ter pelo menos 2 caracteres');
        }
        
        if (!this.isValidEmail(data.email)) {
            errors.push('Email inválido');
        }
        
        if (!this.isValidPhone(data.telefone)) {
            errors.push('Telefone deve estar no formato (XX) XXXXX-XXXX');
        }
        
        if (!this.isValidPassword(data.password)) {
            errors.push('Senha deve ter pelo menos 6 caracteres');
        }
        
        if (data.password !== data.confirm_password) {
            errors.push('Senhas não coincidem');
        }
        
        return {
            isValid: errors.length === 0,
            errors: errors
        };
    }
    
    // Validate login form data
    static validateLogin(data) {
        const errors = [];
        
        if (!this.isValidEmail(data.email)) {
            errors.push('Email inválido');
        }
        
        if (!data.password || data.password.length === 0) {
            errors.push('Senha é obrigatória');
        }
        
        return {
            isValid: errors.length === 0,
            errors: errors
        };
    }
    
    // Show validation errors
    static showErrors(errors, containerId = null) {
        if (errors.length === 0) return;
        
        const errorHtml = errors.map(error => `<li>${this.sanitizeHtml(error)}</li>`).join('');
        const message = `<ul class="mb-0">${errorHtml}</ul>`;
        
        // Use the global mostrarAlerta function if available
        if (typeof mostrarAlerta === 'function') {
            mostrarAlerta(message, 'danger', 6000);
        } else {
            alert(errors.join('\n'));
        }
    }
    
    // Clean and prepare form data
    static sanitizeFormData(data) {
        const sanitized = {};
        
        for (const [key, value] of Object.entries(data)) {
            if (typeof value === 'string') {
                sanitized[key] = this.sanitizeHtml(value.trim());
            } else {
                sanitized[key] = value;
            }
        }
        
        return sanitized;
    }
    
    // Format currency input
    static formatCurrency(value) {
        const num = parseFloat(value.replace(/[^\d.,]/g, '').replace(',', '.'));
        return isNaN(num) ? 0 : num;
    }
    
    // Format phone input
    static formatPhone(value) {
        // Remove all non-digits
        const digits = value.replace(/\D/g, '');
        
        // Apply Brazilian phone mask
        if (digits.length <= 2) {
            return `(${digits}`;
        } else if (digits.length <= 7) {
            return `(${digits.slice(0, 2)}) ${digits.slice(2)}`;
        } else if (digits.length <= 11) {
            return `(${digits.slice(0, 2)}) ${digits.slice(2, 7)}-${digits.slice(7)}`;
        }
        
        return `(${digits.slice(0, 2)}) ${digits.slice(2, 7)}-${digits.slice(7, 11)}`;
    }
    
    // Auto-format inputs on keyup
    static setupInputFormatting() {
        // Phone formatting
        document.addEventListener('input', (e) => {
            if (e.target.id === 'registerPhone' || e.target.classList.contains('phone-input')) {
                e.target.value = this.formatPhone(e.target.value);
            }
        });
        
        // Price formatting
        document.addEventListener('input', (e) => {
            if (e.target.classList.contains('currency-input')) {
                const value = e.target.value.replace(/[^\d.,]/g, '');
                e.target.value = value;
            }
        });
    }
}

// Initialize formatting when DOM loads
document.addEventListener('DOMContentLoaded', () => {
    Validation.setupInputFormatting();
});
