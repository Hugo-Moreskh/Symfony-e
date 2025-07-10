import React, { useState } from 'react';

function App() {
    const [count, setCount] = useState(0);

    return (
        <div style={{ padding: '20px', border: '2px solid #007bff', borderRadius: '5px' }}>
            <h2>TEST</h2>
            <p>Compteur interactif : <strong>{count}</strong></p>
            <button 
                onClick={() => setCount(count + 1)}
                style={{ 
                    padding: '10px 20px', 
                    backgroundColor: '#007bff', 
                    color: 'white', 
                    border: 'none', 
                    borderRadius: '3px',
                    cursor: 'pointer'
                }}
            >
                Incrémenter
            </button>
            <button 
                onClick={() => setCount(0)}
                style={{ 
                    padding: '10px 20px', 
                    backgroundColor: '#dc3545', 
                    color: 'white', 
                    border: 'none', 
                    borderRadius: '3px',
                    cursor: 'pointer',
                    marginLeft: '10px'
                }}
            >
                Reset
            </button>
        </div>
    );
}

export default App;
