<form method="POST" action="create.php" style="display:list-item">
    <div>
        <label for="nazwa_firmy">Nazwa firmy</label>
        <input type="text" name="nazwa_firmy" />
    </div>
    <div>
        <label for="nazwa_domeny">Nazwa domeny</label>
        <input type="text" name="nazwa_domeny" />
    </div>
    <div>
        <label for="logo">Logo</label>
        <input type="file" name="logo" />
    </div>
    <div>
        <label for="imie">Imię</label>
        <input type="text" name="imie" />
    </div>
    <div>
        <label for="nazwisko">Nazwisko</label>
        <input type="text" name="nazwisko" />
    </div>
    <div>
        <label for="numer_telefonu">Numer telefonu</label>
        <input type="text" name="numer_telefonu" />
    </div>
    <div>
        <label for="email">Email</label>
        <input type="text" name="email" />
    </div>
    <div>
        <label for="kategorie">Kategorie</label>
        <select name="kategorie[]" multiple>
            <option value="AM">AM</option>
            <option value="A1">A1</option>
            <option value="A2">A2</option>
            <option value="A">A</option>
            <option value="B1">B1</option>
            <option value="B">B</option>
            <option value="C1">C1</option>
            <option value="C">C</option>
            <option value="D1">D1</option>
            <option value="D">D</option>
            <option value="BE">BE</option>
            <option value="C1E">C1E</option>
            <option value="CE">CE</option>
            <option value="D1E">D1E</option>
            <option value="DE">DE</option>
            <option value="T">T</option>
        </select>
    </div>
    <div>
        <label for="haslo">Hasło</label>
        <input type="password" name="haslo" />
    </div>
    <div>
        <button type="submit">Wygeneruj</button>
    </div>
</form>