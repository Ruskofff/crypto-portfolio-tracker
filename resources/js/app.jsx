import { StrictMode } from 'react';
import { createRoot } from 'react-dom/client';
import PortfolioApp from './PortfolioApp';

createRoot(document.getElementById('app')).render(
    <StrictMode>
        <PortfolioApp />
    </StrictMode>,
);
